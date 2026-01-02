<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\Handlers\EmptyResponseHandler;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Exceptions\EmptyResponseException;
use SimpleApiBitrix24\Tests\BaseTestCase;


class EmptyResponseHandlerTest extends BaseTestCase
{
    private Client $mockedGuzzleClient;
    private User $user;
    private ApiDatabaseConfig $databaseConfig;

    private const TEST_RESPONSE = [];

    public function setUp(): void
    {
        // preparing test database in memory
        $this->user = $this->getUserObject();
        $this->databaseConfig = ApiDatabaseConfig::build($this->createPdo($_ENV['TEST_DB_DIVER']), $_ENV['TEST_TABLE_NAME']);
        $tableManager = new TableManager($this->databaseConfig);
        $tableManager->createUsersTableIfNotExists();
        $repository = new UserRepository($this->databaseConfig);
        $repository->save($this->user);
    }

    public function test_handler_is_working_correctly_using_a_webhook(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultCredentials(new Webhook('https://some-webhook.bitrix24.ru'));
        $api = new ApiClientBitrix24($apiSettings);
        $this->setUsleepTimeForHandler(1, $api);

        $this->setMockedHttpClient();
        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $result = $api->call('qwe')['result'];

        $this->assertTrue($result);
    }

    public function test_handler_throws_an_exception_when_it_is_disabled_using_a_webhook()
    {
        $this->expectException(EmptyResponseException::class);

        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultCredentials(new Webhook('https://some-webhook.bitrix24.ru'));
        $api = new ApiClientBitrix24($apiSettings);
        $this->setUsleepTimeForHandler(1, $api);

        $this->setMockedHttpClientToProvokeException();
        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $api->call('qwe');
    }

    public function test_handler_is_working_correctly_using_a_token(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);

        $apiSettings->setDefaultCredentials($this->user);
        $api = new ApiClientBitrix24($apiSettings, $this->databaseConfig);
        $this->setUsleepTimeForHandler(1, $api);

        $this->setMockedHttpClient();
        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $result = $api->call('qwe')['result'];

        $this->assertTrue($result);
    }

    public function test_handler_throws_an_exception_when_it_is_disabled_using_a_token()
    {
        $this->expectException(EmptyResponseException::class);

        $apiSettings = new ApiClientSettings(AuthType::TOKEN);
        $apiSettings->setDefaultCredentials($this->user);
        $api = new ApiClientBitrix24($apiSettings, $this->databaseConfig);
        $this->setUsleepTimeForHandler(1, $api);

        $this->setMockedHttpClientToProvokeException();
        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $api->call('qwe');
    }

    private function setMockedHttpClientToProvokeException(): void
    {
        $this->mockedGuzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(200, [], json_encode(['result' => true])),
        ]);
    }

    private function setMockedHttpClient(): void
    {
        $this->mockedGuzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(200, [], json_encode(['result' => true])),
        ]);
    }

    /**
     * to set the delay time between requests for the handler
     *
     * @param int $value
     * @param ApiClientBitrix24 $api
     * @return void
     * @throws \ReflectionException
     */
    private function setUsleepTimeForHandler(int $value, ApiClientBitrix24 $api): void
    {
        $newEmptyResponseHandler = new EmptyResponseHandler(true, $value);

        $reflectionApi = new ReflectionClass($api);
        $connectorProperty = $reflectionApi->getProperty('connector');
        $connector = $connectorProperty->getValue($api);

        $reflectionConnector = new ReflectionClass($connector);
        $errorResponseManagerProperty = $reflectionConnector->getProperty('errorResponseManager');
        $errorResponseManager = $errorResponseManagerProperty->getValue($connector);

        $reflectionErrorResponseManager = new ReflectionClass($errorResponseManager);
        $emptyResponseHandlerProperty = $reflectionErrorResponseManager->getProperty('emptyResponseHandler');
        $emptyResponseHandlerProperty->setValue($errorResponseManager, $newEmptyResponseHandler);
    }
}
