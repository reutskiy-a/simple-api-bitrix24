<?php

namespace SimpleApiBitrix24\Services\Installation;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Managers\ErrorResponseManager;
use SimpleApiBitrix24\Connectors\Traits\ConnectorTrait;
use SimpleApiBitrix24\DatabaseCore\Models\User;

class Bitrix24UserDataFetcher
{
    use ConnectorTrait;

    private $httpClient;
    private ErrorResponseManager $errorResponseManager;


    public function __construct(
        private string $domain,
        private string $authToken
    ) {
        $this->httpClient = new Client();
        $this->errorResponseManager = new ErrorResponseManager();
    }

    public function getProfile(): array
    {
        $url = $this->getClientEndPoint($this->domain) . "profile.json";
        $data['auth'] = $this->authToken;

        $response = $this->makeHttpRequest($url, $data);
        $response = json_decode($response->getBody()->getContents(), true);

        if ($this->errorResponseManager->shouldTheRequestBeRepeated(new ErrorContext($response))) {
            return $this->getProfile();
        }

        return $response['result'] ?? $response;
    }

    public function createUserFromProfile(
        $memberId,
        $refreshToken,
        $clientId,
        $clientSecret
    ): User {
        $userProfile = $this->getProfile();

        return new User(
            userId: (int) $userProfile['ID'],
            memberId: $memberId,
            isAdmin: $userProfile['ADMIN'],
            authToken: $this->authToken,
            refreshToken: $refreshToken,
            domain: $this->domain,
            clientId: $clientId,
            clientSecret: $clientSecret,
            createdAt: new CarbonImmutable(),
            updatedAt: new CarbonImmutable()
        );
    }
}
