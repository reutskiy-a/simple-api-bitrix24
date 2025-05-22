<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors\Services;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Tests\Environment\Traits\AppConfigurationTrait;

class QueryLimitExceededServiceTest extends TestCase
{
    use AppConfigurationTrait;

    public function testQueryLimitExceededThrowsExceptionOnWebhookSingleRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhook('https://webhook.here');
        $webhookConnector = ConnectorFactory::create($apiSettings);

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_QUERY_LIMIT_EXCEEDED, 429);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);

        $reflectionQueryLimitExceededService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $contValue = $reflectionQueryLimitExceededService->getConstant('ERROR_RESPONSE');

        $this->expectExceptionMessage(json_encode($contValue));

        $webhookConnector->sendRequest('test', []);
    }

    public function testQueryLimitExceededThrowsExceptionOnWebhookBatchRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhook('https://webhook.here');
        $webhookConnector = ConnectorFactory::create($apiSettings);

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_QUERY_LIMIT_EXCEEDED, 429);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);

        $reflectionQueryLimitExceededService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $contValue = $reflectionQueryLimitExceededService->getConstant('ERROR_RESPONSE');

        $this->expectExceptionMessage(json_encode($contValue));

        $webhookConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);
    }

    public function testQueryLimitExceededThrowsExceptionOnTokenSingleRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForToken();
        $tokenConnector = ConnectorFactory::create(
            $apiSettings,
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_QUERY_LIMIT_EXCEEDED, 429);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);

        $reflectionQueryLimitExceededService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $contValue = $reflectionQueryLimitExceededService->getConstant('ERROR_RESPONSE');

        $this->expectExceptionMessage(json_encode($contValue));

        $tokenConnector->sendRequest('test', []);
    }

    public function testQueryLimitExceededThrowsExceptionOnTokenBatchRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForToken();
        $tokenConnector = ConnectorFactory::create(
            $apiSettings,
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_QUERY_LIMIT_EXCEEDED, 429);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);

        $reflectionQueryLimitExceededService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $contValue = $reflectionQueryLimitExceededService->getConstant('ERROR_RESPONSE');

        $this->expectExceptionMessage(json_encode($contValue));

        $tokenConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);
    }

    public function testQueryLimitHandledSuccessfullyOnWebhookSingleRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhookWithServices('https://webhook.here');

        $reflectionService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $propertyService = $reflectionService->getProperty('usleep');
        $propertyService->setValue($apiSettings->getQueryLimitExceededService(), 0);

        $webhookConnector = ConnectorFactory::create($apiSettings);

        $requestSuccessfully = ['data' => true];

        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);
        $response = $webhookConnector->sendRequest('test', []);

        $this->assertEquals($requestSuccessfully, $response);
    }


    public function testQueryLimitHandledSuccessfullyOnWebhookBatchRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhookWithServices('https://webhook.here');

        $reflectionService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $propertyService = $reflectionService->getProperty('usleep');
        $propertyService->setValue($apiSettings->getQueryLimitExceededService(), 0);

        $webhookConnector = ConnectorFactory::create($apiSettings);

        $requestSuccessfully = ['data' => true];

        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);
        $response = $webhookConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);

        $this->assertEquals($requestSuccessfully, $response);
    }

    public function testQueryLimitHandledSuccessfullyOnTokenSingleRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForTokenWithServices();

        $reflectionService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $propertyService = $reflectionService->getProperty('usleep');
        $propertyService->setValue($apiSettings->getQueryLimitExceededService(), 0);

        $tokenConnector = ConnectorFactory::create(
            $apiSettings,
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $requestSuccessfully = ['data' => true];

        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);
        $response = $tokenConnector->sendRequest('test', []);

        $this->assertEquals($requestSuccessfully, $response);
    }

    public function testQueryLimitHandledSuccessfullyOnTokenBatchRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForTokenWithServices();

        $reflectionService = new ReflectionClass($apiSettings->getQueryLimitExceededService());
        $propertyService = $reflectionService->getProperty('usleep');
        $propertyService->setValue($apiSettings->getQueryLimitExceededService(), 0);

        $tokenConnector = ConnectorFactory::create(
            $apiSettings,
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $requestSuccessfully = ['data' => true];

        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(429, [], json_encode(self::ERROR_QUERY_LIMIT_EXCEEDED)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);
        $response = $tokenConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);

        $this->assertEquals($requestSuccessfully, $response);
    }
}
