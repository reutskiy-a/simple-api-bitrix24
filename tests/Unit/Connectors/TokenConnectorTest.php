<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Connectors\TokenConnector;
use SimpleApiBitrix24\Tests\Environment\Traits\AppConfigurationTrait;

class TokenConnectorTest extends TestCase
{
    use AppConfigurationTrait;

    private TokenConnector $tokenConnector;

    public function testAssertValidCredentialsThrowsExceptionWhenUserNotFound(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithoutUserData()
        );

        $reflectionTokenConnector = new ReflectionClass($tokenConnector);

        $userProperty = $reflectionTokenConnector->getProperty('user');
        $userObject = $userProperty->getValue($tokenConnector);
        $memberId = $userObject->getMemberId();

        $this->expectExceptionMessage("User not found in database with the given member_id: '{$memberId}'");

        $method = $reflectionTokenConnector->getMethod('assertValidCredentials');
        $method->invoke($tokenConnector, $userObject);
    }

    public function testSendRequestReturnsArrayForSuccessfulResponse(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $httpClient = $this->getHttpClientMock(['test' => 'ok']);

        $reflectionTokenConnector = new ReflectionClass($tokenConnector);
        $httpClientProperty = $reflectionTokenConnector->getProperty('httpClient');
        $httpClientProperty->setValue($tokenConnector, $httpClient);

        $response = $tokenConnector->sendRequest('test', []);

        $this->assertIsArray($response);
    }

    public function testSendBatchRequestReturnsArrayForSuccessfulResponse(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $httpClient = $this->getHttpClientMock(['test' => 'ok']);

        $reflectionTokenConnector = new ReflectionClass($tokenConnector);
        $httpClientProperty = $reflectionTokenConnector->getProperty('httpClient');
        $httpClientProperty->setValue($tokenConnector, $httpClient);

        $response = $tokenConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);

        $this->assertIsArray($response);
    }
}
