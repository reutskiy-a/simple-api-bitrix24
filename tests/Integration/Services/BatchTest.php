<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\Services;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Batch;
use SimpleApiBitrix24\Tests\BaseTestCase;

class BatchTest extends BaseTestCase
{
    private Batch $batch;

    public function setUp(): void
    {
        $apiSettings = new ApiClientSettings(AuthType::TOKEN);

        $dbSettings = ApiDatabaseConfig::build($this->createPdo(
            $_ENV['LOCAL_APP_DB_DRIVER']),
            $_ENV['LOCAL_APP_DB_TABLE_NAME']
        );

        $api = new ApiClientBitrix24($apiSettings, $dbSettings);
        $repository = new UserRepository($dbSettings);
        $user = $repository->getFirstAdminByMemberId($_ENV['MEMBER_ID']);
        $api->setCredentials($user);

        $this->batch = new Batch($api);
    }

    public function test_batch_call_with_keys_ok(): void
    {
        $result = $this->batch->callWithKeys([
            'scope_response' => ['method' => 'scope', 'params' => []],
            'profile_response' => ['method' => 'profile', 'params' => []],
        ]);

        $this->assertTrue(key_exists('scope_response', $result));
        $this->assertTrue(key_exists('profile_response', $result));
    }
}
