<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors;

use PHPUnit\Framework\TestCase;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Connectors\TokenConnector;
use SimpleApiBitrix24\Connectors\WebhookConnector;
use SimpleApiBitrix24\Tests\Environment\Traits\AppConfigurationTrait;

class ConnectorFactoryTest extends TestCase
{
    use AppConfigurationTrait;

    private ApiClientSettings $apiSettings;

    public function setUp(): void
    {
        $this->apiSettings = new ApiClientSettings();
    }

    public function testFactoryReturnsWebhookConnector():void
    {
        $this->apiSettings->setWebhookAuthEnabled(true);
        $webhookConnectorObject = ConnectorFactory::create($this->apiSettings);

        $this->assertInstanceOf(WebhookConnector::class, $webhookConnectorObject);
    }

    public function testFactoryReturnsTokenConnector(): void
    {
        $this->apiSettings->setTokenAuthEnabled(true);
        $databaseConfig = $this->getApiDatabaseConfigMockWithoutUserData();
        $tokenConnectorObject = ConnectorFactory::create($this->apiSettings, $databaseConfig);

        $this->assertInstanceOf(TokenConnector::class, $tokenConnectorObject);
    }
}
