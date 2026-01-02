<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors;

use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\Connectors\TokenConnector;
use SimpleApiBitrix24\Connectors\WebhookConnector;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Tests\BaseTestCase;

class ConnectorFactoryTest extends BaseTestCase
{
    public function test_factory_returns_webhook_connector():void
    {
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultCredentials(new Webhook('https://webhook.here'));
        $connector = ConnectorFactory::create($apiSettings);

        $this->assertInstanceOf(WebhookConnector::class, $connector);
    }

    public function test_factory_returns_token_connector(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);

        $connector = ConnectorFactory::create(
            $apiSettings,
            ApiDatabaseConfig::build($this->createPdo($_ENV['DB_DRIVER_SQLITE']))
        );

        $this->assertInstanceOf(TokenConnector::class, $connector);
    }
}
