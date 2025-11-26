<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\Database;

use PDO;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Tests\BaseTestCase;

class UserRepositoryPgsqlTest extends BaseTestCase
{
    private UserRepository $repository;

    private PDO $pdo;

    public function setUp(): void
    {
        $this->pdo = $this->createPdo($_ENV['DB_DRIVER_PGSQL']);
        $apiDatabaseConfig = ApiDatabaseConfig::build($this->pdo, $_ENV['TEST_TABLE_NAME']);
        $this->repository = new UserRepository($apiDatabaseConfig);

        $tableManager = new TableManager($apiDatabaseConfig);
        $tableManager->createUsersTableIfNotExists();
    }

    public function tearDown(): void
    {
        $this->dropTable($this->pdo, $_ENV['TEST_TABLE_NAME']);
    }

    public function test_insert()
    {
        $user = $this->getUserObject();
        $this->repository->insert($user);

        $getUser = $this->repository->getUserByIdAndMemberId(
            filter_var($_ENV['USER_ID'], FILTER_VALIDATE_INT),
            $_ENV['USER_MEMBER_ID']
        );

        $this->assertEquals($getUser->getUserId(), $user->getUserId());
    }

    public function test_update_and_delete_user()
    {
        $user = $this->getUserObject();
        $this->repository->save($user);

        $user->setRefreshToken('new_refresh_token');
        $this->repository->update($user);

        $getUser = $this->repository->getUserByIdAndMemberId(
            filter_var($_ENV['USER_ID'], FILTER_VALIDATE_INT),
            $_ENV['USER_MEMBER_ID']
        );

        $this->assertEquals($getUser->getRefreshToken(), $user->getRefreshToken());
    }

    public function test_get_first_admin_by_member_id()
    {
        $user = $this->getUserObject();
        $user->setIsAdmin(true);

        $this->repository->save($user);

        $getAdmin = $this->repository->getFirstAdminByMemberId(
            $user->getMemberId()
        );

        $this->assertEquals($getAdmin->getUserId(), $user->getUserId());
    }

    public function test_get_first_user_by_member_id()
    {
        $user = $this->getUserObject();
        $user->setIsAdmin(false);

        $this->repository->save($user);

        $getAdmin = $this->repository->getFirstUserByMemberId(
            $user->getMemberId()
        );

        $this->assertEquals($getAdmin->getUserId(), $user->getUserId());
    }

    public function test_get_first_user_by_id_and_member_id()
    {
        $user = $this->getUserObject();
        $user->setIsAdmin(false);

        $this->repository->save($user);

        $getAdmin = $this->repository->getUserByIdAndMemberId(
            $user->getUserId(),
            $user->getMemberId()
        );

        $this->assertEquals($getAdmin->getUserId(), $user->getUserId());
        $this->assertTrue($this->repository->delete($user));
    }

    public function test_get_all_users_by_member_id()
    {
        $user1 = $this->getUserObject();
        $user2 = $this->getUserObject(987654321);
        $user3 = $this->getUserObject(123123123);

        $this->repository->save($user1);
        $this->repository->save($user2);
        $this->repository->save($user3);

        $getUsers = $this->repository->getAllUsersByMemberId($user1->getMemberId());
        $this->assertEquals(3, count($getUsers));
    }

    public function test_method_does_user_exists_by_id_and_member_id_returns_true()
    {
        $user = $this->getUserObject();
        $this->repository->save($user);

        $this->assertTrue($this->repository->doesUserExistByIdAndMemberId(
            $user->getUserId(),
            $user->getMemberId()
        ));
    }

    public function test_delete_user_by_member_id()
    {
        $user = $this->getUserObject();

        $this->repository->save($user);
        $this->repository->delete($user);

        $getUser = $this->repository->getUserByIdAndMemberId(
            $user->getUserId(),
            $user->getMemberId()
        );

        $this->assertNull($getUser);
    }

    public function test_delete_all_users_by_member_id()
    {
        $user1 = $this->getUserObject();
        $user2 = $this->getUserObject(987654321);

        $this->repository->save($user1);
        $this->repository->save($user2);

        $this->repository->deleteAllUsersByMemberId($user1->getMemberId());
        $getUser = $this->repository->getFirstUserByMemberId($user1->getMemberId());

        $this->assertNull($getUser);
    }
}
