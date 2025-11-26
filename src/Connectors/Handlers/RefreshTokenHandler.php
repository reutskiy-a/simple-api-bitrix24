<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Interfaces\ErrorHandlerInterface;
use SimpleApiBitrix24\Constants\AppConstants;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Exceptions\RefreshTokenException;

class RefreshTokenHandler implements ErrorHandlerInterface
{
    private const ERROR_TEMPLATE = [
        'error' => 'expired_token',
        'error_description' => 'The access token provided has expired.'
    ];
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

    public function canHandle(ErrorContext $errorContext): bool
    {
        if ($errorContext->response['error'] === self::ERROR_TEMPLATE['error'] &&
            $errorContext->response['error_description'] === self::ERROR_TEMPLATE['error_description']) {
            return true;
        }

        return false;
    }

    public function handle(ErrorContext $errorContext): bool
    {
        return $this->refreshUserTokens($errorContext->user);
    }

    /**
     * @throws RefreshTokenException
     * @throws GuzzleException
     */
    private function refreshUserTokens(User $user): bool
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
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
                'User-Agent' => AppConstants::APP_INFO
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
            $errorMessage = sprintf(
                "%s An error occurred during the token refresh request. The application's client_id or client_secret are incorrect.",
                json_encode($response)
            );

            throw new RefreshTokenException($errorMessage);
        }

        if ($response['error'] == self::ERROR_INVALID_GRANT) {
            $errorMessage = sprintf(
                "%s An error occurred during the token refresh request. The refresh token is either invalid or expired, or the application's client_id and client_secret are incorrect.",
                json_encode($response)
            );

            throw new RefreshTokenException($errorMessage);
        }

        if (array_key_exists("error", $response)) {
            throw new RefreshTokenException(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
    }

    private function updateUserTokens(User $user, array $response): bool
    {
        $user->setAuthToken($response['access_token']);
        $user->setRefreshToken($response['refresh_token']);

        return $this->userRepository->update($user);
    }
}
