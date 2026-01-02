<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Tests\BaseTestCase;

class ApiClientBitrix24Test extends BaseTestCase
{
    public function test_get_credentials_method_returns_user_object()
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);
        $apiSettings->setDefaultCredentials($this->getUserObject());
        $api = new ApiClientBitrix24($apiSettings, ApiDatabaseConfig::build($this->createPdo('sqlite')));

        $this->assertInstanceOf(User::class, $api->getCredentials());
    }

    public function test_get_credentials_method_returns_webhook_object()
    {
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultCredentials(new Webhook('webhook_url'));
        $api = new ApiClientBitrix24($apiSettings);

        $this->assertInstanceOf(Webhook::class, $api->getCredentials());
    }

    public function test_clone_api_client_and_set_another_webhook_credentials()
    {
        $apiSettings = new ApiClientSettings(AuthType::WEBHOOK);
        $apiSettings->setDefaultCredentials(new Webhook('1'));
        $api_1 = new ApiClientBitrix24($apiSettings);

        $api_2 = clone $api_1;
        $api_2->setCredentials(new Webhook('2'));

        $this->assertEquals($api_1->getCredentials()->getUrl(), '1');
        $this->assertEquals($api_2->getCredentials()->getUrl(), '2');
    }

    public function test_clone_api_client_and_set_another_token_credentials()
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);
        $databaseConfig = ApiDatabaseConfig::build($this->createPdo('sqlite'));
        $user_1 = $this->getUserObject(1);
        $apiSettings->setDefaultCredentials($user_1);

        $api_1 = new ApiClientBitrix24($apiSettings, $databaseConfig);

        $api_2 = clone $api_1;
        $user_2 = $this->getUserObject(2);
        $api_2->setCredentials($user_2);

        $this->assertEquals($api_1->getCredentials()->getUserId(), 1);
        $this->assertEquals($api_2->getCredentials()->getUserId(), 2);
    }
}
