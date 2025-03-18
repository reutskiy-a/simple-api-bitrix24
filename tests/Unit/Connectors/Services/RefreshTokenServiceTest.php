<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Connectors\Services;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\Connectors\Services\RefreshTokenService;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Tests\Environment\Traits\AppConfigurationTrait;

class RefreshTokenServiceTest extends TestCase
{
    use AppConfigurationTrait;

    private const ERROR_WRONG_CLIENT = 'wrong_client';
    private const ERROR_INVALID_GRANT = 'invalid_grant';
    private const ATTEMPTS_LIMIT = 6;
    private MockObject $userRepositoryMock;
    private MockObject $userMock;
    private RefreshTokenService $refreshTokenService;
    private array $responseOk = [
        'access_token' => 'new_access_token',
        'refresh_token' => 'new_refresh_token',
        'expires_in' => 3600,
    ];
    private array $responseWithErrorWrongClient = [
        'error' => self::ERROR_WRONG_CLIENT
    ];
    private array $responseWithErrorInvalidGrant = [
        'error' => self::ERROR_INVALID_GRANT
    ];

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->userRepositoryMock->method('update')->willReturn(true);

        $this->userMock = $this->createMock(User::class);
        $this->userMock->method('getClientId')->willReturn('client_id');
        $this->userMock->method('getClientSecret')->willReturn('client_secret');
        $this->userMock->method('getRefreshToken')->willReturn('refresh_token');

        $this->refreshTokenService = new RefreshTokenService($this->userRepositoryMock);
    }

    public function testRefreshTokenThrowsExceptionWhenAttemptsLimitExceeded(): void
    {
        $this->expectExceptionMessage("Token refresh attempt limit exceeded. Maximum allowed attempts: " . self::ATTEMPTS_LIMIT);

        $httpClient = $this->getHttpClientMock($this->responseOk);

        $reflection = new ReflectionClass($this->refreshTokenService);
        $property = $reflection->getProperty('httpClient');
        $property->setValue($this->refreshTokenService, $httpClient);

        foreach (range(0, self::ATTEMPTS_LIMIT) as $attempt) {
            $httpClient->post('https://my.test', [$attempt])->getBody()->rewind();
            $this->refreshTokenService->refreshUserTokens($this->userMock);
        }
    }

    public function testRefreshTokenThrowsExceptionForWrongClientError(): void
    {
        $this->expectExceptionMessage(json_encode($this->responseWithErrorWrongClient) . ' ' . "An error occurred during the token refresh request. The application's client_id or client_secret are incorrect.");

        $httpClient = $this->getHttpClientMock($this->responseWithErrorWrongClient);

        $reflection = new ReflectionClass($this->refreshTokenService);
        $property = $reflection->getProperty('httpClient');
        $property->setValue($this->refreshTokenService, $httpClient);

        $this->refreshTokenService->refreshUserTokens($this->userMock);
    }

    public function testRefreshTokenThrowsExceptionForInvalidGrantError(): void
    {
        $this->expectExceptionMessage(json_encode($this->responseWithErrorInvalidGrant) . ' ' . "An error occurred during the token refresh request. The refresh token is either invalid or expired, or the application's client_id and client_secret are incorrect.");

        $httpClient = $this->getHttpClientMock($this->responseWithErrorInvalidGrant);

        $reflection = new ReflectionClass($this->refreshTokenService);
        $property = $reflection->getProperty('httpClient');
        $property->setValue($this->refreshTokenService, $httpClient);

        $this->refreshTokenService->refreshUserTokens($this->userMock);
    }



}
