<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\Services\Installation;

use PHPUnit\Framework\Attributes\Test;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Tests\BaseTestCase;

class InstallationServiceTest extends BaseTestCase
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

    #[Test]
    public function user_saved_ok()
    {
        $this->api->call('profile');    // auth token refreshing
        $user = $this->api->getCredentials();

        $userSecondInstance = $this->api->services()->installation()->saveUser(
            clientId: $user->getClientId(),
            clientSecret: $user->getClientSecret(),
            memberId: $user->getMemberId(),
            authToken: $user->getAuthToken(),
            refreshToken: $user->getRefreshToken(),
            domain: $user->getDomain()
        );

        $this->assertEquals($user->getUserId(), $userSecondInstance->getUserId());
        $this->assertEquals($user->getClientId(), $userSecondInstance->getClientId());
        $this->assertEquals($user->getClientSecret(), $userSecondInstance->getClientSecret());
        $this->assertEquals($user->getDomain(), $userSecondInstance->getDomain());
        $this->assertEquals($user->getAuthToken(), $userSecondInstance->getAuthToken());
        $this->assertEquals($user->getRefreshToken(), $userSecondInstance->getRefreshToken());
        $this->assertEquals($user->getMemberId(), $userSecondInstance->getMemberId());
    }
    
}
