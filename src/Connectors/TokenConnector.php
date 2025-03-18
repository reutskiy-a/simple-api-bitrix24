<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors;

use GuzzleHttp\Client;
use SimpleApiBitrix24\Connectors\Services\RefreshTokenService;
use SimpleApiBitrix24\Connectors\Traits\ConnectorTrait;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\Exceptions\ConnectorException;
use SimpleApiBitrix24\Exceptions\RefreshTokenException;

class TokenConnector implements ConnectorInterface
{
    use ConnectorTrait;

    private const ERROR_EXPIRED_TOKEN = 'expired_token';
    private User $user;
    private RefreshTokenService $refreshTokenService;
    private Client $httpClient;

    public function __construct(User $user, RefreshTokenService $refreshTokenService)
    {
        $this->user = $user;
        $this->refreshTokenService = $refreshTokenService;
        $this->httpClient = new Client();
    }

    /**
     * @throws RefreshTokenException
     * @throws ConnectorException
     */
    public function sendRequest(string $method, array $params): array
    {
        $this->assertValidCredentials($this->user);

        $url = $this->user->getClientEndpoint() . $method . ".json";
        $data = $params;
        $data['auth'] = $this->user->getAccessToken();

        $response = $this->makeHttpRequest($url, $data);
        $response = json_decode($response->getBody()->getContents(), true);

        if (array_key_exists('error', $response) && $response['error'] == self::ERROR_EXPIRED_TOKEN) {
            $this->refreshTokenService->refreshUserTokens($this->user);
            return $this->sendRequest($method, $params);
        }

        return $response;
    }

    /**
     * @throws RefreshTokenException
     * @throws ConnectorException
     */
    public function sendBatchRequest(array $queries): array
    {
        $this->assertValidCredentials($this->user);

        $url = $this->user->getClientEndpoint() . "batch.json";
        $httpQuery = $this->buildBatchQueries($queries);
        $data = ['cmd' => $httpQuery, 'halt' => 0, 'auth' => $this->user->getAccessToken()];

        $response = $this->makeHttpRequest($url, $data);
        $response = json_decode($response->getBody()->getContents(), true);

        if (array_key_exists('error', $response) && $response['error'] == self::ERROR_EXPIRED_TOKEN) {
            $this->refreshTokenService->refreshUserTokens($this->user);
            return $this->sendBatchRequest($queries);
        }

        return $response;
    }

    /**
     * @throws ConnectorException
     */
    private function assertValidCredentials($user): bool
    {
        if (empty($user->getIdPrimaryKey())) {
            throw new ConnectorException("User not found in database with the given member_id: '{$user->getMemberId()}'");
        }

        return true;
    }

}
