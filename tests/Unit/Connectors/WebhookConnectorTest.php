<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Tests\Environment\Traits\AppConfigurationTrait;

class WebhookConnectorTest extends TestCase
{
    use AppConfigurationTrait;

    public function testAssertValidCredentialsThrowsExceptionWhenWebhookIncorrect(): void
    {
        $webhookConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForWebhook()
        );

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $property = $reflectionWebhookConnector->getProperty('webhook');
        $propertyValue = $property->getValue($webhookConnector);

        $this->expectExceptionMessage("the webhook is incorrect: '$propertyValue'");

        $method = $reflectionWebhookConnector->getMethod('assertValidCredentials');
        $method->invoke($webhookConnector, $propertyValue);
    }

    public function testSendRequestReturnsArrayForSuccessfulResponse(): void
    {
        $webhookConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForWebhook('https://webhook.here')
        );

        $httpClient = $this->getHttpClientMock(['test' => 'ok']);

        $reflectionTokenConnector = new ReflectionClass($webhookConnector);
        $httpClientProperty = $reflectionTokenConnector->getProperty('httpClient');
        $httpClientProperty->setValue($webhookConnector, $httpClient);

        $response = $webhookConnector->sendRequest('test', []);

        $this->assertIsArray($response);
    }

    public function testSendBatchRequestReturnsArrayForSuccessfulResponse(): void
    {
        $webhookConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForWebhook('https://webhook.here')
        );

        $httpClient = $this->getHttpClientMock(['test' => 'ok']);

        $reflectionTokenConnector = new ReflectionClass($webhookConnector);
        $httpClientProperty = $reflectionTokenConnector->getProperty('httpClient');
        $httpClientProperty->setValue($webhookConnector, $httpClient);

        $response = $webhookConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);

        $this->assertIsArray($response);
    }

}
