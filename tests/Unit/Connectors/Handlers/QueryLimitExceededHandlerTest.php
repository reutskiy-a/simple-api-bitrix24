<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Exceptions\QueryLimitExceededException;
use SimpleApiBitrix24\Tests\BaseTestCase;


class QueryLimitExceededHandlerTest extends BaseTestCase
{
    private Client $mockedGuzzleClient;
    private User $user;
    private ApiDatabaseConfig $databaseConfig;

    private const TEST_RESPONSE = [
        "error" => "QUERY_LIMIT_EXCEEDED",
        "error_description" => "Too many requests"
    ];

    public function setUp(): void
    {
        $this->mockedGuzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
            new Response(200, [], json_encode(['result' => true])),
        ]);

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
        $apiSettings->setDefaultConnection(new Webhook('https://some-webhook.bitrix24.ru'))
            ->setQueryLimitExceededHandler(true, 1);
        $api = new ApiClientBitrix24($apiSettings);

        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $result = $api->call('qwe')['result'];

        $this->assertTrue($result);
    }

    public function test_handler_throws_an_exception_when_it_is_disabled_using_a_webhook()
    {
        $this->expectException(QueryLimitExceededException::class);

        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultConnection(new Webhook('https://some-webhook.bitrix24.ru'))
            ->setQueryLimitExceededHandler(false);
        $api = new ApiClientBitrix24($apiSettings);

        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $api->call('qwe');
    }

    public function test_handler_is_working_correctly_using_a_token(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);

        $apiSettings->setDefaultConnection($this->user)
            ->setQueryLimitExceededHandler(true, 1);
        $api = new ApiClientBitrix24($apiSettings, $this->databaseConfig);

        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $result = $api->call('qwe')['result'];

        $this->assertTrue($result);
    }

    public function test_handler_throws_an_exception_when_it_is_disabled_using_a_token()
    {
        $this->expectException(QueryLimitExceededException::class);

        $apiSettings = new ApiClientSettings(AuthType::TOKEN);
        $apiSettings->setDefaultConnection($this->user)
            ->setQueryLimitExceededHandler(false);
        $api = new ApiClientBitrix24($apiSettings, $this->databaseConfig);

        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $api->call('qwe');
    }

}
