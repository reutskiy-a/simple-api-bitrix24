<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\DatabaseCore;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;
use SimpleApiBitrix24\Tests\Environment\TemporarySqliteDatabaseManager;

/**
 * @property UserRepository $UserRepository
 */
class UserRepositoryTest extends TestCase
{
    private const TEST_MEMBER_ID = 'test_member_id';
    private UserRepository $userRepository;
    private User $user;

    public function setUp(): void
    {
        $dbManager = new TemporarySqliteDatabaseManager();
        $dbManager->prepareDatabaseWithData(self::TEST_MEMBER_ID);
        $this->userRepository = $dbManager->getUserRepository();
        $this->user = $dbManager->getUser();
    }

    public function testCheckInstance(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
    }

    public function testInsert(): void
    {
        $this->expectExceptionMessage('SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: api_tokens_bitrix24.member_id');
        $this->userRepository->insert($this->user);
    }

    public function testUpdate(): void
    {
        $result = $this->userRepository->update($this->user);
        $this->assertTrue($result);
    }

    public function testGetUserByMemberId(): void
    {
        $result = $this->userRepository->getUserByMemberId(self::TEST_MEMBER_ID);
        $this->assertInstanceOf(User::class, $result);
    }

    public function testSave(): void
    {
        $result = $this->userRepository->save($this->user);
        $this->assertTrue($result);
    }

    public function testIsUserNotFoundByMemberId(): void
    {
        $reflection = new ReflectionClass($this->userRepository);
        $method = $reflection->getMethod('isUserNotFoundByMemberId');
        $result = $method->invoke($this->userRepository, self::TEST_MEMBER_ID);

        $this->assertFalse($result);
    }
}
