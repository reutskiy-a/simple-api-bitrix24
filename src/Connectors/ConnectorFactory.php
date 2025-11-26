<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors;

use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\Handlers\RefreshTokenHandler;
use SimpleApiBitrix24\Connectors\Interfaces\ConnectorInterface;
use SimpleApiBitrix24\Connectors\Managers\ErrorResponseManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Exceptions\ConnectorException;

abstract class ConnectorFactory
{
    public static function create(
        ApiClientSettings $apiSettings,
        ?ApiDatabaseConfig $apiDatabaseConfig = null): ConnectorInterface
    {
        $errorResponseManager = new ErrorResponseManager();
        $errorResponseManager
            ->addErrorHandler($apiSettings->getOperationTimeLimitHandler())
            ->addErrorHandler($apiSettings->getQueryLimitExceededHandler());


        if ($apiSettings->isWebhookAuthEnabled()) {
            return new WebhookConnector($apiSettings->getDefaultConnection(), $errorResponseManager);
        }

        if ($apiSettings->isTokenAuthEnabled()) {
            $userRepository = new UserRepository($apiDatabaseConfig);
            $refreshTokenService = new RefreshTokenHandler($userRepository);

            $errorResponseManager
                ->addErrorHandler($refreshTokenService);

            return new TokenConnector($apiSettings->getDefaultConnection(), $errorResponseManager);
        }

        throw new ConnectorException('No connector is specified in the settings');
    }

}
