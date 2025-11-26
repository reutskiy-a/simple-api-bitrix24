<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Exceptions\Bitrix24ResponseException;
use SimpleApiBitrix24\Exceptions\ConnectorException;
use SimpleApiBitrix24\Tests\BaseTestCase;

class ApiClientWithWebhookTest extends BaseTestCase
{
    private ApiClientBitrix24 $api;

    public function setUp(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultConnection(new Webhook($_ENV['WEBHOOK']));
        $this->api = new ApiClientBitrix24($apiSettings);
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
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $api = new ApiClientBitrix24($apiSettings);

        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessage("the webhook is incorrect: 'null'");

        $api->call('test');
    }
}
