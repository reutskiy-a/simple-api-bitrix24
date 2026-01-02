<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\DatabaseCore;

use Aura\SqlQuery\QueryFactory;
use Carbon\CarbonImmutable;
use PDO;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\Models\User;

class UserRepository
{
    private ApiDatabaseConfig $apiDatabaseConfig;
    private QueryFactory $queryFactory;

    public function __construct(ApiDatabaseConfig $apiDatabaseConfig)
    {
        $this->apiDatabaseConfig = $apiDatabaseConfig;
        $this->queryFactory = new QueryFactory($apiDatabaseConfig->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * @param User $user
     * @return bool
     */
    public function insert(User $user): bool
    {
        $insert = $this->queryFactory->newInsert();
        $insert
            ->into($this->apiDatabaseConfig->tableName)
            ->cols([
                $this->apiDatabaseConfig->userIdColumnName => $user->getUserId(),
                $this->apiDatabaseConfig->memberIdColumnName => $user->getMemberId(),
                $this->apiDatabaseConfig->isAdminColumnName => $user->isAdmin() ? 1 : 0,
                $this->apiDatabaseConfig->authTokenColumnName => $user->getAuthToken(),
                $this->apiDatabaseConfig->refreshTokenColumnName => $user->getRefreshToken(),
                $this->apiDatabaseConfig->domainColumnName => $user->getDomain(),
                $this->apiDatabaseConfig->clientIdColumnName => $user->getClientId(),
                $this->apiDatabaseConfig->clientSecretColumnName => $user->getClientSecret(),
                $this->apiDatabaseConfig->createdAtColumnName => $user->getCreatedAt()->toDateTimeString(),
                $this->apiDatabaseConfig->updatedAtColumnName => $user->getUpdatedAt()->toDateTimeString(),
            ]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($insert->getStatement());

        return $stmt->execute($insert->getBindValues());
    }

    /**
     * @param User $user
     * @return bool
     */
    public function update(User $user): bool
    {
        $update = $this->queryFactory->newUpdate();
        $update
            ->table($this->apiDatabaseConfig->tableName)
            ->cols([
                $this->apiDatabaseConfig->userIdColumnName => $user->getUserId(),
                $this->apiDatabaseConfig->memberIdColumnName => $user->getMemberId(),
                $this->apiDatabaseConfig->isAdminColumnName => $user->isAdmin() ? 1 : 0,
                $this->apiDatabaseConfig->authTokenColumnName => $user->getAuthToken(),
                $this->apiDatabaseConfig->refreshTokenColumnName => $user->getRefreshToken(),
                $this->apiDatabaseConfig->domainColumnName => $user->getDomain(),
                $this->apiDatabaseConfig->clientIdColumnName => $user->getClientId(),
                $this->apiDatabaseConfig->clientSecretColumnName => $user->getClientSecret(),
                $this->apiDatabaseConfig->updatedAtColumnName => $user->getUpdatedAt()->toDateTimeString(),
            ])
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->where($this->apiDatabaseConfig->userIdColumnName . "= :userId")
            ->bindValues(['memberId' => $user->getMemberId(), 'userId' => $user->getUserId()]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($update->getStatement());
        return $stmt->execute($update->getBindValues());
    }

    /**
     * Get a user by Bitrix24 user ID and member ID
     *
     * @param int $userId
     * @param string $memberId
     * @return User|null
     */
    public function getUserByIdAndMemberId(int $userId, string $memberId): ?User
    {
        $select = $this->queryFactory->newSelect();
        $select
            ->cols(['*'])
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->userIdColumnName . "= :userId")
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->bindValues(['userId' => $userId, 'memberId' => $memberId]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $result) {
            return null;
        }

        return $this->buildUserObject($result);
    }

    /**
     * Get the first user with administrator rights from the database by member ID
     *
     * @param string $memberId
     * @return User|null
     */
    public function getFirstAdminByMemberId(string $memberId): ?User
    {
        $select = $this->queryFactory->newSelect();
        $select
            ->cols(['*'])
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->where($this->apiDatabaseConfig->isAdminColumnName . "= :isAdmin")
            ->bindValues(['memberId' => $memberId, 'isAdmin' => 1]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $result) {
            return null;
        }

        return $this->buildUserObject($result);
    }

    /**
     * Get the first user in the database by member ID, not an administrator.
     *
     * @param string $memberId
     * @return User|null
     */
    public function getFirstUserByMemberId(string $memberId): ?User
    {
        $select = $this->queryFactory->newSelect();
        $select
            ->cols(['*'])
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->where($this->apiDatabaseConfig->isAdminColumnName . "= :isAdmin")
            ->bindValues(['memberId' => $memberId, 'isAdmin' => 0]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $result) {
            return null;
        }

        return $this->buildUserObject($result);
    }

    /**
     * Get all users by member ID
     *
     * @param string $memberId
     * @return array
     */
    public function getAllUsersByMemberId(string $memberId): array
    {
        $select = $this->queryFactory->newSelect();
        $select
            ->cols(['*'])
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->bindValues(['memberId' => $memberId]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($user) => $this->buildUserObject($user), $users);
    }

    /**
     * Delete a user by Bitrix24 user ID and member ID
     *
     * @param int $userId
     * @param string $memberId
     * @return bool
     */
    public function deleteUserByIdAndMemberId(int $userId, string $memberId): bool
    {
        $delete = $this->queryFactory->newDelete();
        $delete
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->userIdColumnName . "= :userId")
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->bindValues(['userId' => $userId, 'memberId' => $memberId]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($delete->getStatement());
        return $stmt->execute($delete->getBindValues());
    }

    /**
     * Delete all users in the database by member ID
     *
     * @param string $memberId
     * @return bool
     */
    public function deleteAllUsersByMemberId(string $memberId): bool
    {
        $delete = $this->queryFactory->newDelete();
        $delete
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->bindValue('memberId', $memberId);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($delete->getStatement());
        return $stmt->execute($delete->getBindValues());
    }

    /**
     * Delete a user from the database
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $this->deleteUserByIdAndMemberId($user->getUserId(), $user->getMemberId());
    }

    /**
     * Updates the user if they already exist in the database and adds a new one if they do not exist
     *
     * @param User $user
     * @return bool|string
     */
    public function save(User $user): bool|string
    {
        if ($this->doesUserExistByIdAndMemberId($user->getUserId(), $user->getMemberId())) {
            return $this->update($user);
        } else {
            return $this->insert($user);
        }
    }

    /**
     * Does a user exist by Bitrix24 user ID and member ID
     *
     * @param int $userId
     * @param string $memberId
     * @return bool
     */
    public function doesUserExistByIdAndMemberId(int $userId, string $memberId): bool
    {
        $user = $this->getUserByIdAndMemberId($userId, $memberId);

        if (null === $user) {
            return false;
        }

        return true;
    }

    /**
     * @param array $fetchedData
     * @return User
     */
    private function buildUserObject(array $fetchedData): User
    {
        return new User(
            userId: $fetchedData[$this->apiDatabaseConfig->userIdColumnName],
            memberId: $fetchedData[$this->apiDatabaseConfig->memberIdColumnName],
            isAdmin: (bool) $fetchedData[$this->apiDatabaseConfig->isAdminColumnName],
            authToken: $fetchedData[$this->apiDatabaseConfig->authTokenColumnName],
            refreshToken: $fetchedData[$this->apiDatabaseConfig->refreshTokenColumnName],
            domain: $fetchedData[$this->apiDatabaseConfig->domainColumnName],
            clientId: $fetchedData[$this->apiDatabaseConfig->clientIdColumnName],
            clientSecret: $fetchedData[$this->apiDatabaseConfig->clientSecretColumnName],
            createdAt: new CarbonImmutable($fetchedData[$this->apiDatabaseConfig->createdAtColumnName]),
            updatedAt: new CarbonImmutable($fetchedData[$this->apiDatabaseConfig->updatedAtColumnName])
        );
    }
}
