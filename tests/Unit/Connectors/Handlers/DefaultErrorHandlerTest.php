<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;
use SimpleApiBitrix24\Tests\BaseTestCase;

class DefaultErrorHandlerTest extends BaseTestCase
{
    private Client $mockedGuzzleClient;
    private const TEST_RESPONSE = [
        'error' => 'some error',
        'error_description' => 'some error'
    ];

    public function setUp(): void
    {
        $this->mockedGuzzleClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE)),
        ]);
    }

    public function test_throws_default_exception_using_a_webhook(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultConnection(new Webhook('https://some-webhook.bitrix24.ru'));
        $api = new ApiClientBitrix24($apiSettings);

        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);
        $this->expectException(Bitrix24ResponseException::class);

        $api->call('test');
    }

    public function test_throws_default_exception_using_a_token(): void
    {
        // preparing test database in memory
        $user = $this->getUserObject();
        $databaseConfig = ApiDatabaseConfig::build($this->createPdo($_ENV['TEST_DB_DIVER']), $_ENV['TEST_TABLE_NAME']);
        $tableManager = new TableManager($databaseConfig);
        $tableManager->createUsersTableIfNotExists();
        $repository = new UserRepository($databaseConfig);
        $repository->save($user);

        $apiSettings = new ApiClientSettings(AuthType::TOKEN);

        $apiSettings->setDefaultConnection($user);
        $api = new ApiClientBitrix24($apiSettings, $databaseConfig);

        $this->setMockedHttpClientInApiClient($this->mockedGuzzleClient, $api);

        $this->expectException(Bitrix24ResponseException::class);
        $api->call('test');
    }


}
