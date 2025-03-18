<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Environment;

use PDO;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Constants\DatabaseConstants;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\DatabaseCore\UserRepository;

class TemporarySqliteDatabaseManager
{
    private PDO $pdo;
    private UserRepository $userRepository;
    private User $user;

    public function getPDO()
    {
        if (empty($this->pdo)) {
            $this->pdo = new PDO('sqlite::memory:');
        }

        return $this->pdo;
    }

    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function prepareDatabaseWithData(string $newMemberId): TemporarySqliteDatabaseManager
    {
        $this->createUserTable();
        $this->userRepository = new UserRepository($this->getApiDatabaseConfig());
        $this->addUser($newMemberId);

        return $this;
    }

    public function prepareDatabase(): TemporarySqliteDatabaseManager
    {
        $this->createUserTable();
        $this->userRepository = new UserRepository($this->getApiDatabaseConfig());

        return $this;
    }

    public function addUser(string $newMemberId): bool|string
    {
        $this->user = (new User())
            ->setMemberId($newMemberId)
            ->setAccessToken('0ccac967007qk12de61a0753dd')
            ->setExpiresIn(3600)
            ->setApplicationToken('b668e199f5b6ba24')
            ->setRefreshToken('fc48f1670076a03a7f7ce50f287bfb9')
            ->setDomain('test.bitrix24.ru')
            ->setClientEndpoint('https://test.bitrix24.ru/rest/')
            ->setClientId('test.167cf82.377523')
            ->setClientSecret('s6FskfEWdHK5Epy38BYPBzON123zs');

        return $this->userRepository->save($this->user);
    }

    public function createUserTable(): void
    {
        $pdo = $this->getPDO();
        $queryCreateTable = '
            CREATE TABLE IF NOT EXISTS api_tokens_bitrix24 (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            member_id TEXT UNIQUE NOT NULL,
            access_token TEXT NOT NULL,
            expires_in TEXT NOT NULL,
            application_token TEXT NOT NULL,
            refresh_token TEXT NOT NULL,
            domain TEXT NOT NULL,
            client_endpoint TEXT NOT NULL,
            client_id TEXT NOT NULL,
            client_secret TEXT NOT NULL);
        ';

        $statement = $pdo->prepare($queryCreateTable);
        $statement->execute();
    }

    public function getApiDatabaseConfig(): ApiDatabaseConfig
    {
        return new ApiDatabaseConfig(
            pdo: $this->getPDO(),
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
