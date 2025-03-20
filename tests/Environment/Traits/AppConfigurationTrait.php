<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Environment\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PDO;
use PDOStatement;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Constants\DatabaseConstants;

trait AppConfigurationTrait
{
    private function getHttpClientMock(array $response, string|int $httpStatus = 200): Client
    {
        $client = $this->createMock(Client::class);
        $client->method('post')->willReturn(
            new Response($httpStatus, [], json_encode($response))
        );
        return $client;
    }

    private function getApiSettingsMockForWebhook(string $webhook = ''): ApiClientSettings
    {
        return (new ApiClientSettings())->setWebhookAuthEnabled(true)->setDefaultConnection($webhook);
    }

    private function getApiSettingsMockForToken(): ApiClientSettings
    {
        return (new ApiClientSettings())->setTokenAuthEnabled(true);
    }

    private function getApiDatabaseConfigMockWithoutUserData(): ApiDatabaseConfig
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn(false);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('getAttribute')->with(PDO::ATTR_DRIVER_NAME)->willReturn('sqlite');
        $pdoMock->method('prepare')->willReturn($stmtMock);

        return new ApiDatabaseConfig(
            pdo: $pdoMock,
            tableName: DatabaseConstants::TABLE_NAME,
            primaryKeyColumnName: DatabaseConstants::PRIMARY_KEY_COLUMN_NAME,
            memberIdColumnName: DatabaseConstants::MEMBER_ID_COLUMN_NAME,
            accessTokenColumnName: DatabaseConstants::ACCESS_TOKEN_COLUMN_NAME,
            expiresInColumnName: DatabaseConstants::EXPIRES_IN_COLUMN_NAME,
            applicationTokenColumnName: DatabaseConstants::APPLICATION_TOKEN_COLUMN_NAME,
            refreshTokenColumnName: DatabaseConstants::REFRESH_TOKEN_COLUMN_NAME,
            domainColumnName: DatabaseConstants::DOMAIN_COLUMN_NAME,
            clientEndpointColumnName: DatabaseConstants::CLIENT_END_POINT_COLUMN_NAME,
            clientIdColumnName: DatabaseConstants::CLIENT_ID_COLUMN_NAME,
            clientSecretColumnName: DatabaseConstants::CLIENT_SECRET_COLUMN_NAME
        );
    }

    private function getApiDatabaseConfigMockWithUserData(): ApiDatabaseConfig
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn([
            DatabaseConstants::PRIMARY_KEY_COLUMN_NAME => 1,
            DatabaseConstants::MEMBER_ID_COLUMN_NAME => 'test_member',
            DatabaseConstants::ACCESS_TOKEN_COLUMN_NAME => 'test_token',
            DatabaseConstants::EXPIRES_IN_COLUMN_NAME => '3600',
            DatabaseConstants::APPLICATION_TOKEN_COLUMN_NAME => 'app_token',
            DatabaseConstants::REFRESH_TOKEN_COLUMN_NAME => 'refresh_token',
            DatabaseConstants::DOMAIN_COLUMN_NAME => 'example.bitrix24.com',
            DatabaseConstants::CLIENT_END_POINT_COLUMN_NAME => 'https://example.bitrix24.com/rest/',
            DatabaseConstants::CLIENT_ID_COLUMN_NAME => 'client123',
            DatabaseConstants::CLIENT_SECRET_COLUMN_NAME => 'secret123'
        ]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('getAttribute')->with(PDO::ATTR_DRIVER_NAME)->willReturn('sqlite');
        $pdoMock->method('prepare')->willReturn($stmtMock);

        return new ApiDatabaseConfig(
            pdo: $pdoMock,
            tableName: DatabaseConstants::TABLE_NAME,
            primaryKeyColumnName: DatabaseConstants::PRIMARY_KEY_COLUMN_NAME,
            memberIdColumnName: DatabaseConstants::MEMBER_ID_COLUMN_NAME,
            accessTokenColumnName: DatabaseConstants::ACCESS_TOKEN_COLUMN_NAME,
            expiresInColumnName: DatabaseConstants::EXPIRES_IN_COLUMN_NAME,
            applicationTokenColumnName: DatabaseConstants::APPLICATION_TOKEN_COLUMN_NAME,
            refreshTokenColumnName: DatabaseConstants::REFRESH_TOKEN_COLUMN_NAME,
            domainColumnName: DatabaseConstants::DOMAIN_COLUMN_NAME,
            clientEndpointColumnName: DatabaseConstants::CLIENT_END_POINT_COLUMN_NAME,
            clientIdColumnName: DatabaseConstants::CLIENT_ID_COLUMN_NAME,
            clientSecretColumnName: DatabaseConstants::CLIENT_SECRET_COLUMN_NAME
        );
    }
}
