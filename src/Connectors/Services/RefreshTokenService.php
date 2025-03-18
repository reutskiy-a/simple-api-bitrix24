<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Exceptions\RefreshTokenException;

class RefreshTokenService
{
    private const ERROR_WRONG_CLIENT = 'wrong_client';
    private const ERROR_INVALID_GRANT = 'invalid_grant';
    private const TOKEN_REFRESH_URL = 'https://oauth.bitrix.info/oauth/token/';
    private const ATTEMPTS_LIMIT = 6;
    private int $refreshTokenAttempts = 0;
    private UserRepository $userRepository;
    private Client $httpClient;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->httpClient = new Client();
    }

    /**
     * @throws RefreshTokenException
     * @throws GuzzleException
     */
    public function refreshUserTokens(User $user): bool
    {
        $this->refreshTokenAttempts++;

        if ($this->refreshTokenAttempts == self::ATTEMPTS_LIMIT) {
            throw new RefreshTokenException(
                'Token refresh attempt limit exceeded. Maximum allowed attempts: ' . self::ATTEMPTS_LIMIT
            );
        }

        $data = [
            'grant_type' => 'refresh_token',
            'client_id' => $user->getClientId(),
            'client_secret' => $user->getClientSecret(),
            'refresh_token' => $user->getRefreshToken()
        ];

        $response = $this->httpClient->post(self::TOKEN_REFRESH_URL, [
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json'
            ],
            'form_params' => $data
        ]);

        $response = json_decode($response->getBody()->getContents(), true);
        $this->handleResponseErrors($response);

        return $this->updateUserTokens($user, $response);
    }

    private function handleResponseErrors(array $response): void
    {
        if (! array_key_exists('error', $response)) {
            return;
        }

        if ($response['error'] == self::ERROR_WRONG_CLIENT) {
            throw new RefreshTokenException(
                json_encode($response) . ' ' . "An error occurred during the token refresh request. The application's client_id or client_secret are incorrect."
            );
        }

        if ($response['error'] == self::ERROR_INVALID_GRANT) {
            throw new RefreshTokenException(
                json_encode($response) . ' ' . "An error occurred during the token refresh request. The refresh token is either invalid or expired, or the application's client_id and client_secret are incorrect."
            );
        }
    }

    private function updateUserTokens(User $user, array $response): bool
    {
        $user->setAccessToken($response['access_token']);
        $user->setExpiresIn($response['expires_in']);
        $user->setRefreshToken($response['refresh_token']);

        return $this->userRepository->update($user);
    }
}
