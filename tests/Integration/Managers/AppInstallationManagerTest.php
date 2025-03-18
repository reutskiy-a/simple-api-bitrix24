<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\Managers;

use PHPUnit\Framework\TestCase;
use SimpleApiBitrix24\Managers\AppInstallationManager;
use SimpleApiBitrix24\Tests\Environment\TemporarySqliteDatabaseManager;

class AppInstallationManagerTest extends TestCase
{
    public function testInstallSavesUserToDatabase(): void
    {
        $dbManager = (new TemporarySqliteDatabaseManager())->prepareDatabase();
        $apiDatabaseConfig = $dbManager->getApiDatabaseConfig();

        $memberId = 'new_test_member_id';

        $appInstallationManager = (new AppInstallationManager($apiDatabaseConfig))
            ->setMemberId($memberId)
            ->setAccessToken('new_0ccac967007qk12de61a0753dd')
            ->setExpiresIn(3600)
            ->setApplicationToken('new_b668e199f5b6ba24')
            ->setRefreshToken('new_fc48f1670076a03a7f7ce50f287bfb9')
            ->setDomain('new_test.bitrix24.ru')
            ->setClientId('new_test.167cf82.377523')
            ->setClientSecret('new_s6FskfEWdHK5Epy38BYPBzON123zs');

        $userRepository = $dbManager->getUserRepository();
        $user = $userRepository->getUserByMemberId($memberId);

        $this->assertStringContainsString($memberId, $user->getMemberId());
    }
}
