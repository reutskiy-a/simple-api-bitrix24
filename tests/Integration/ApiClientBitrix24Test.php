<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Tests\Environment\TemporarySqliteDatabaseManager;

class ApiClientBitrix24Test extends TestCase
{
    private const TEST_MEMBER_ID_1 = 'test_member_id_1';
    private const TEST_MEMBER_ID_2 = 'test_member_id_2';
    private const TEST_WEBHOOK_1 = 'test_webhook_1';
    private const TEST_WEBHOOK_2 = 'test_webhook_2';
    private TemporarySqliteDatabaseManager $dbManager;
    private ApiDatabaseConfig $apiDatabaseConfig;

    public function setUp(): void
    {
        $this->dbManager = new TemporarySqliteDatabaseManager();
        $this->dbManager->prepareDatabaseWithData(self::TEST_MEMBER_ID_1);
        $this->apiDatabaseConfig = $this->dbManager->getApiDatabaseConfig();
    }

    public function testConnectToSwitchesWebhookForClonedApiClient(): void
    {
        $apiSettings = new ApiClientSettings();
        $apiSettings->setWebhookAuthEnabled(true)
                    ->setDefaultConnection(self::TEST_WEBHOOK_1);

        // first api connection
        $firstApiClient = new ApiClientBitrix24($apiSettings);

        // second api connection
        $secondApiClient = clone $firstApiClient;
        $secondApiClient->connectTo(self::TEST_WEBHOOK_2);


        $webhookFromFirstApiClient = $this->getWebhookUrlByApiClientObject($firstApiClient);
        $webhookFromSecondApiClient = $this->getWebhookUrlByApiClientObject($secondApiClient);

        $this->assertStringContainsString(self::TEST_WEBHOOK_1, $webhookFromFirstApiClient);
        $this->assertStringContainsString(self::TEST_WEBHOOK_2, $webhookFromSecondApiClient);
    }

    public function testConnectToSwitchesMemberIdForClonedApiClient()
    {
        $apiSettings = new ApiClientSettings();
        $apiSettings->setTokenAuthEnabled(true)
                    ->setDefaultConnection(self::TEST_MEMBER_ID_1);

        // first api connection
        $firstApiClient = new ApiClientBitrix24($apiSettings, $this->apiDatabaseConfig);

        // second api connection
        $this->dbManager->addUser(self::TEST_MEMBER_ID_2);
        $secondApiClient = clone $firstApiClient;
        $secondApiClient->connectTo(self::TEST_MEMBER_ID_2);


        $memberIdFromFirstApiClient = $this->getMemberIdByApiClientObject($firstApiClient);
        $memberIdFromSecondApiClient = $this->getMemberIdByApiClientObject($secondApiClient);

        $this->assertStringContainsString(self::TEST_MEMBER_ID_1, $memberIdFromFirstApiClient);
        $this->assertStringContainsString(self::TEST_MEMBER_ID_2, $memberIdFromSecondApiClient);
    }

    private function getWebhookUrlByApiClientObject(ApiClientBitrix24 $api): string
    {
        $apiReflection = new ReflectionClass($api);
        $property = $apiReflection->getProperty('connector');

        $connector = $property->getValue($api);
        $connectorReflection = new ReflectionClass($connector);
        $property = $connectorReflection->getProperty('webhook');

        return $property->getValue($connector);
    }

    private function getMemberIdByApiClientObject(ApiClientBitrix24 $api): string
    {
        $apiReflection = new ReflectionClass($api);
        $property = $apiReflection->getProperty('connector');

        $connector = $property->getValue($api);
        $connectorReflection = new ReflectionClass($connector);
        $property = $connectorReflection->getProperty('user');

        $user = $property->getValue($connector);
        $userReflection = new ReflectionClass($user);
        $property = $userReflection->getProperty('memberId');

        return $property->getValue($user);
    }

}
