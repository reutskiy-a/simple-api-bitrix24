<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors;

use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\Services\RefreshTokenService;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Exceptions\ConnectorException;

abstract class ConnectorFactory
{
    public static function create(
        ApiClientSettings $apiSettings,
        ?ApiDatabaseConfig $apiDatabaseConfig = null): ConnectorInterface
    {
        if ($apiSettings->isWebhookAuthEnabled()) {
            return new WebhookConnector($apiSettings->getDefaultConnection());
        }

        if ($apiSettings->isTokenAuthEnabled()) {
            $userRepository = new UserRepository($apiDatabaseConfig);
            $refreshTokenService = new RefreshTokenService($userRepository);
            $user = $userRepository->getUserByMemberId($apiSettings->getDefaultConnection());

            return new TokenConnector($user, $refreshTokenService);
        }

        throw new ConnectorException('No connector is specified in the settings');
    }
}
