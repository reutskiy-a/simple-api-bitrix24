<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Managers;

use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;


/**
 * Manages the installation process of a Bitrix24 application by configuring and saving API token data.
 * This class is used in Services\InstallationService for handling app installation.
 * See Services\InstallationService.php for a usage example.
 */
class AppInstallationManager
{
    private string $memberId;
    private string $accessToken;
    private string|int $expiresIn;
    private string $applicationToken;
    private string $refreshToken;
    private string $domain;
    private string $clientEndpoint;
    private string $clientId;
    private string $clientSecret;
    private UserRepository $userRepository;

    public function __construct(ApiDatabaseConfig $apiDatabaseConfig)
    {
        $this->userRepository = new UserRepository($apiDatabaseConfig);
    }

    public function setMemberId(string $memberId): AppInstallationManager
    {
        $this->memberId = $memberId;
        return $this;
    }

    public function setAccessToken(string $accessToken): AppInstallationManager
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function setExpiresIn(string|int $expiresIn): AppInstallationManager
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

    public function setApplicationToken(string $applicationToken): AppInstallationManager
    {
        $this->applicationToken = $applicationToken;
        return $this;
    }

    public function setRefreshToken(string $refreshToken): AppInstallationManager
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function setDomain(string $domain): AppInstallationManager
    {
        $this->domain = $domain;
        $this->setClientEndpoint($domain);
        return $this;
    }

    private function setClientEndpoint(string $domain): void
    {
        $this->clientEndpoint = "https://" . $domain . "/rest/";
    }

    public function setClientId(string $clientId): AppInstallationManager
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $clientSecret): AppInstallationManager
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function install(): bool|string
    {
        $user = (new User())
            ->setMemberId($this->memberId)
            ->setAccessToken($this->accessToken)
            ->setExpiresIn($this->expiresIn)
            ->setApplicationToken($this->applicationToken)
            ->setRefreshToken($this->refreshToken)
            ->setDomain($this->domain)
            ->setClientEndpoint($this->clientEndpoint)
            ->setClientId($this->clientId)
            ->setClientSecret($this->clientSecret);

        return $this->userRepository->save($user);
    }

    public function finish(): void
    {
        echo '
        <head>
            <script src="//api.bitrix24.com/api/v1/"></script>
            <script>
                BX24.init(function(){
                    BX24.installFinish();
                });
            </script>
        </head>';
    }
}
