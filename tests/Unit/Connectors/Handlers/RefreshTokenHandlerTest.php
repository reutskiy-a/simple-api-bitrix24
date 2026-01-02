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
use SimpleApiBitrix24\Connectors\Handlers\RefreshTokenHandler;
use SimpleApiBitrix24\Connectors\Managers\ErrorResponseManager;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Exceptions\RefreshTokenException;
use SimpleApiBitrix24\Tests\BaseTestCase;

class RefreshTokenHandlerTest extends BaseTestCase
{
    private ApiDatabaseConfig $databaseConfig;
    private User $user;
    private ApiClientBitrix24 $api;

    private const TEST_RESPONSE_EXPIRED_TOKEN = [
        'error' => 'expired_token',
        'error_description' => 'The access token provided has expired.'
    ];
    private const TEST_RESPONSE_ERROR_WRONG_CLIENT = ['error' => 'wrong_client'];
    private const TEST_RESPONSE_ERROR_INVALID_GRANT = ['error' => 'invalid_grant'];

    public function setUp(): void
    {
        // preparing test database in memory
        $this->user = $this->getUserObject();
        $this->databaseConfig = ApiDatabaseConfig::build($this->createPdo($_ENV['TEST_DB_DIVER']), $_ENV['TEST_TABLE_NAME']);
        $tableManager = new TableManager($this->databaseConfig);
        $tableManager->createUsersTableIfNotExists();
        $repository = new UserRepository($this->databaseConfig);
        $repository->save($this->user);

        $apiSettings = new ApiClientSettings(AuthType::TOKEN);
        $apiSettings->setDefaultCredentials($this->user);

        $this->api = new ApiClientBitrix24($apiSettings, $this->databaseConfig);
    }

    public function test_tokens_updated()
    {
        $newAccessToken = (string) mt_rand(1000, 10000);
        $newRefreshToken = (string) mt_rand(1000, 10000);

        // set up http client mocks
        $mockedGuzzleClientForApiClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE_EXPIRED_TOKEN)),
            new Response(200, [], json_encode(['result' => true])),
        ]);

        $mockedGuzzleClientForRefreshTokenHandler = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ])),
        ]);

        $this->setMockedHttpClientInApiClient($mockedGuzzleClientForApiClient, $this->api);
        $this->setMockedHttpClientInRefreshTokenHandler($mockedGuzzleClientForRefreshTokenHandler, $this->api);

        // test
        $result = $this->api->call('test')['result'];

        $this->assertEquals($this->user->getRefreshToken(), $newRefreshToken);
        $this->assertEquals($this->user->getAuthToken(), $newAccessToken);
        $this->assertTrue($result);
    }

    public function test_throws_refresh_token_exception_on_invalid_grant()
    {
        // set up http client mocks
        $mockedGuzzleClientForApiClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE_EXPIRED_TOKEN)),
            new Response(200, [], json_encode(['result' => true])),
        ]);

        $mockedGuzzleClientForRefreshTokenHandler = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode(self::TEST_RESPONSE_ERROR_INVALID_GRANT)),
        ]);

        $this->setMockedHttpClientInApiClient($mockedGuzzleClientForApiClient, $this->api);
        $this->setMockedHttpClientInRefreshTokenHandler($mockedGuzzleClientForRefreshTokenHandler, $this->api);

        // test
        $this->expectException(RefreshTokenException::class);

        $errorMessage = sprintf(
            "%s An error occurred during the token refresh request. The refresh token is either invalid or expired, or the application's client_id and client_secret are incorrect.",
            json_encode(self::TEST_RESPONSE_ERROR_INVALID_GRANT)
        );
        $this->expectExceptionMessage($errorMessage);

        $this->api->call('test');
    }

    public function test_throws_refresh_token_exception_on_wrong_client()
    {
        // set up http client mocks
        $mockedGuzzleClientForApiClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE_EXPIRED_TOKEN)),
            new Response(200, [], json_encode(['result' => true])),
        ]);

        $mockedGuzzleClientForRefreshTokenHandler = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode(self::TEST_RESPONSE_ERROR_WRONG_CLIENT)),
        ]);

        $this->setMockedHttpClientInApiClient($mockedGuzzleClientForApiClient, $this->api);
        $this->setMockedHttpClientInRefreshTokenHandler($mockedGuzzleClientForRefreshTokenHandler, $this->api);

        // test
        $this->expectException(RefreshTokenException::class);

        $errorMessage = sprintf(
            "%s An error occurred during the token refresh request. The application's client_id or client_secret are incorrect.",
            json_encode(self::TEST_RESPONSE_ERROR_WRONG_CLIENT)
        );
        $this->expectExceptionMessage($errorMessage);

        $this->api->call('test');
    }

    public function test_throws_refresh_token_exception_on_any_errors()
    {
        $someError = ['error' => 'some_error'];

        // set up http client mocks
        $mockedGuzzleClientForApiClient = $this->getGuzzleHttpClientMockQueue([
            new Response(429, [], json_encode(self::TEST_RESPONSE_EXPIRED_TOKEN)),
            new Response(200, [], json_encode(['result' => true])),
        ]);

        $mockedGuzzleClientForRefreshTokenHandler = $this->getGuzzleHttpClientMockQueue([
            new Response(200, [], json_encode($someError)),
        ]);

        $this->setMockedHttpClientInApiClient($mockedGuzzleClientForApiClient, $this->api);
        $this->setMockedHttpClientInRefreshTokenHandler($mockedGuzzleClientForRefreshTokenHandler, $this->api);

        // test
        $this->expectException(RefreshTokenException::class);
        $this->expectExceptionMessage(json_encode($someError, JSON_UNESCAPED_UNICODE));

        $this->api->call('test');
    }

    private function setMockedHttpClientInRefreshTokenHandler(Client $mockedHttpClient, ApiClientBitrix24 $api): void
    {
        $userRepository = new UserRepository($this->databaseConfig);
        $refreshTokenHandler = new RefreshTokenHandler($userRepository);

        // Replace http client with a mock at RefreshTokenHandler
        $reflectionRefreshTokenHandler = new ReflectionClass($refreshTokenHandler);
        $httpClientProperty = $reflectionRefreshTokenHandler->getProperty('httpClient');
        $httpClientProperty->setValue($refreshTokenHandler, $mockedHttpClient);

        $newErrorResponseManager = new ErrorResponseManager();
        $newErrorResponseManager->addErrorHandler($refreshTokenHandler);

        // Replace new ErrorResponseManager at TokenConnector
        $reflectionApi = new ReflectionClass($api);
        $connectorProperty = $reflectionApi->getProperty('connector');
        $connector = $connectorProperty->getValue($api);

        $reflectionConnector = new ReflectionClass($connector);
        $errorResponseManagerProperty = $reflectionConnector->getProperty('errorResponseManager');
        $errorResponseManagerProperty->setValue($connector, $newErrorResponseManager);
    }
}
