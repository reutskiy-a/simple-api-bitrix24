<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\Services;

use PHPUnit\Framework\Attributes\Test;
use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiClientSettings;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Enums\AuthType;
use SimpleApiBitrix24\Services\Batch;
use SimpleApiBitrix24\Tests\BaseTestCase;

class BatchCollectorTest extends BaseTestCase
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

    #[Test]
    public function collector_works_correctly(): void
    {
        $collector = $this->batch->collector();

        $collector->add('profile');
        $collector->add('app.info');
        $collector->add('user.admin');

        $result = $collector->execute();

        $this->assertEquals(3, count($result));
        $this->assertTrue($result[0]['ADMIN']);
        $this->assertTrue($result[2]);
        $this->assertEquals([], $collector->queries());
    }

    #[Test]
    public function collector_works_correctly_with_150_queries(): void
    {
        $collector = $this->batch->collector();
        $count = 150;

        for($i = 0; $i < $count; $i++) {
            $collector->add('user.admin');
        }

        $result = $collector->execute();

        $this->assertEquals($count, count($result));
    }
}
