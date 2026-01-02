<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;
use SimpleApiBitrix24\Exceptions\ConnectorException;
use SimpleApiBitrix24\Tests\BaseTestCase;

class ApiClientWithTokenTest extends BaseTestCase
{
    private ApiClientBitrix24 $api;

    public function setUp(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);

        $dbSettings = ApiDatabaseConfig::build($this->createPdo(
            $_ENV['LOCAL_APP_DB_DRIVER']),
            $_ENV['LOCAL_APP_DB_TABLE_NAME']
        );

        $this->api = new ApiClientBitrix24($apiSettings, $dbSettings);
        $repository = new UserRepository($dbSettings);
        $user = $repository->getFirstAdminByMemberId($_ENV['MEMBER_ID']);
        $this->api->setCredentials($user);
    }

    public function test_request()
    {
        $profile = $this->api->call('profile');
        $this->assertTrue(! empty($profile['result']));
    }

    public function test_batch_request()
    {
        $query = [
            [
                'method' => 'profile',
                'params' => []
            ],
            [
                'method' => 'scope',
                'params' => []
            ]
        ];

        $result = $this->api->callBatch($query)['result']['result'] ?? null;
        $this->assertTrue(! empty($result[0]) && ! empty($result[1]));
    }

    public function test_response_with_error()
    {
        $this->expectException(Bitrix24ResponseException::class);
        $this->api->call('qweqwe');
    }

    public function test_throws_exception_when_no_connection_is_specified()
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);
        $api = new ApiClientBitrix24(
            $apiSettings,
            ApiDatabaseConfig::build($this->createPdo(
                $_ENV['LOCAL_APP_DB_DRIVER']),
                $_ENV['LOCAL_APP_DB_TABLE_NAME']
            )
        );

        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessage("User not found in the database or User object was not used with the
                methods ApiClientBitrix24::setCredentials or ApiClientSettings::setDefaultCredentials");

        $api->call('test');
    }
}
