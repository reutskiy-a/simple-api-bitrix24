<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Services\Installation;

use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Managers\AppInstallationManager;

class InstallationService
{
    private AppInstallationManager $installationManager;

    /**
     * Service for handling Bitrix24 application installation process.
     *
     * Usage example:
     * ```php
     * $installationService = new InstallationService();
     * $installationService->startInstallation(
     *     'local.67c******983.166****279',                     // Client ID
     *     '7KriLM5******6T6tCgVSqUj2IL******0qkeBzYbzqso',     // Client Secret
     *     $databaseConfig,                                     // Your ApiDatabaseConfig instance
     *     $_REQUEST                                            // Data from Bitrix24 request
     * );
     *
     * // Optional: Add your installation logic here
     * $apiClient->call('scope');
     *
     * $installationService->finishInstallation();              // Complete the installation
     * ```
     */
    public function startInstallation(string $clientId,
                                      string $clientSecret,
                                      ApiDatabaseConfig $apiDatabaseConfig,
                                      array $requestData): void
    {
        $this->installationManager = new AppInstallationManager($apiDatabaseConfig);
        $this->installationManager
            ->setMemberId($requestData['member_id'])
            ->setAccessToken($requestData['AUTH_ID'])
            ->setExpiresIn($requestData['AUTH_EXPIRES'])
            ->setApplicationToken($requestData['APP_SID'])
            ->setRefreshToken($requestData['REFRESH_ID'])
            ->setDomain($requestData['DOMAIN'])
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->install();
    }

    public function finishInstallation(): void
    {
        $this->installationManager->finish();
    }
}
