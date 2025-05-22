<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors\Services;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Tests\Environment\Traits\AppConfigurationTrait;

class EmptyResponseServiceTest extends TestCase
{
    use AppConfigurationTrait;

    public function testEmptyResponseServiceThrowsExceptionOnWebhookSingleRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhook('https://webhook.here');
        $webhookConnector = ConnectorFactory::create($apiSettings);

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_EMPTY_RESPONSE, 429);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($webhookConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyHandleEnabled = $reflectionEmptyResponseService->getProperty('handleEnabled');

        // отключаем сервис, чтобы он выбросил ошибку
        $propertyHandleEnabled->setValue($emptyResponseService, false);

        $exceptionMessage = $reflectionEmptyResponseService->getConstant('EXCEPTION_MESSAGE');

        $this->expectExceptionMessage($exceptionMessage);

        $webhookConnector->sendRequest('test', []);
    }

    public function testEmptyResponseServiceThrowsExceptionOnWebhookBatchRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhook('https://webhook.here');
        $webhookConnector = ConnectorFactory::create($apiSettings);

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_EMPTY_RESPONSE, 429);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($webhookConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyHandleEnabled = $reflectionEmptyResponseService->getProperty('handleEnabled');

        // отключаем сервис, чтобы он выбросил ошибку
        $propertyHandleEnabled->setValue($emptyResponseService, false);

        $exceptionMessage = $reflectionEmptyResponseService->getConstant('EXCEPTION_MESSAGE');

        $this->expectExceptionMessage($exceptionMessage);

        $webhookConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);
    }

    public function testEmptyResponseServiceThrowsExceptionOnTokenSingleRequest(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_EMPTY_RESPONSE, 429);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($tokenConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyHandleEnabled = $reflectionEmptyResponseService->getProperty('handleEnabled');

        // отключаем сервис, чтобы он выбросил ошибку
        $propertyHandleEnabled->setValue($emptyResponseService, false);

        $exceptionMessage = $reflectionEmptyResponseService->getConstant('EXCEPTION_MESSAGE');

        $this->expectExceptionMessage($exceptionMessage);

        $tokenConnector->sendRequest('test', []);
    }

    public function testEmptyResponseServiceThrowsExceptionOnTokenBatchRequest(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $guzzleClient = $this->getGuzzleHttpClientMock(self::ERROR_EMPTY_RESPONSE, 429);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($tokenConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyHandleEnabled = $reflectionEmptyResponseService->getProperty('handleEnabled');

        // отключаем сервис, чтобы он выбросил ошибку
        $propertyHandleEnabled->setValue($emptyResponseService, false);

        $exceptionMessage = $reflectionEmptyResponseService->getConstant('EXCEPTION_MESSAGE');

        $this->expectExceptionMessage($exceptionMessage);

        $tokenConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);
    }

    public function testEmptyResponseHandledSuccessfullyOnWebhookSingleRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhookWithServices('https://webhook.here');
        $webhookConnector = ConnectorFactory::create($apiSettings);

        $requestSuccessfully = ['data' => true];
        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($webhookConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyUsleep = $reflectionEmptyResponseService->getProperty('usleep');

        // время ожидания изменяем на 0, чтобы тесты прошли быстро.
        $propertyUsleep->setValue($emptyResponseService, 0);


        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);
        $response = $webhookConnector->sendRequest('test', []);

        $this->assertEquals($requestSuccessfully, $response);
    }

    public function testEmptyResponseHandledSuccessfullyOnWebhookBatchRequest(): void
    {
        $apiSettings = $this->getApiSettingsMockForWebhookWithServices('https://webhook.here');
        $webhookConnector = ConnectorFactory::create($apiSettings);

        $requestSuccessfully = ['data' => true];
        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($webhookConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyUsleep = $reflectionEmptyResponseService->getProperty('usleep');

        // время ожидания изменяем на 0, чтобы тесты прошли быстро.
        $propertyUsleep->setValue($emptyResponseService, 0);


        $reflectionWebhookConnector = new ReflectionClass($webhookConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($webhookConnector, $guzzleClient);
        $response = $webhookConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);

        $this->assertEquals($requestSuccessfully, $response);
    }

    public function testEmptyResponseHandledSuccessfullyOnTokenSingleRequest(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $requestSuccessfully = ['data' => true];
        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($tokenConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyUsleep = $reflectionEmptyResponseService->getProperty('usleep');

        // время ожидания изменяем на 0, чтобы тесты прошли быстро.
        $propertyUsleep->setValue($emptyResponseService, 0);


        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);
        $response = $tokenConnector->sendRequest('test', []);

        $this->assertEquals($requestSuccessfully, $response);
    }

    public function testEmptyResponseHandledSuccessfullyOnTokenBatchRequest(): void
    {
        $tokenConnector = ConnectorFactory::create(
            $this->getApiSettingsMockForToken(),
            $this->getApiDatabaseConfigMockWithUserData()
        );

        $requestSuccessfully = ['data' => true];
        $guzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode(self::ERROR_EMPTY_RESPONSE)),
            new Response(200, [], json_encode($requestSuccessfully))
        ]);

        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);

        $errorResponseManager = $reflectionWebhookConnector->getProperty('errorResponseManager')->getValue($tokenConnector);
        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);

        $emptyResponseService = $reflectionErrorResponseManager->getProperty('emptyResponseService')->getValue($errorResponseManager);
        $reflectionEmptyResponseService = new ReflectionClass($emptyResponseService);
        $propertyUsleep = $reflectionEmptyResponseService->getProperty('usleep');

        // время ожидания изменяем на 0, чтобы тесты прошли быстро.
        $propertyUsleep->setValue($emptyResponseService, 0);


        $reflectionWebhookConnector = new ReflectionClass($tokenConnector);
        $httpClient = $reflectionWebhookConnector->getProperty('httpClient');
        $httpClient->setValue($tokenConnector, $guzzleClient);
        $response = $tokenConnector->sendBatchRequest([
            ['method' => 'test','params' => ['test']]
        ]);

        $this->assertEquals($requestSuccessfully, $response);
    }
}
