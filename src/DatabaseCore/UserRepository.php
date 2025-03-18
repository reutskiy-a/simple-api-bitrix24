<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\DatabaseCore;

use Aura\SqlQuery\QueryFactory;
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

    public function insert(User $user): false|string
    {
        $insert = $this->queryFactory->newInsert();
        $insert
            ->into($this->apiDatabaseConfig->tableName)
            ->cols([
                $this->apiDatabaseConfig->memberIdColumnName => $user->getMemberId(),
                $this->apiDatabaseConfig->accessTokenColumnName => $user->getAccessToken(),
                $this->apiDatabaseConfig->expiresInColumnName => $user->getExpiresIn(),
                $this->apiDatabaseConfig->applicationTokenColumnName => $user->getApplicationToken(),
                $this->apiDatabaseConfig->refreshTokenColumnName => $user->getRefreshToken(),
                $this->apiDatabaseConfig->domainColumnName => $user->getDomain(),
                $this->apiDatabaseConfig->clientEndpointColumnName => $user->getClientEndpoint(),
                $this->apiDatabaseConfig->clientIdColumnName => $user->getClientId(),
                $this->apiDatabaseConfig->clientSecretColumnName => $user->getClientSecret(),
            ]);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($insert->getStatement());
        $stmt->execute($insert->getBindValues());
        return $this->apiDatabaseConfig->pdo->lastInsertId();
    }

    public function update(User $user): bool
    {
        $update = $this->queryFactory->newUpdate();
        $update
            ->table($this->apiDatabaseConfig->tableName)
            ->cols([
                $this->apiDatabaseConfig->memberIdColumnName => $user->getMemberId(),
                $this->apiDatabaseConfig->accessTokenColumnName => $user->getAccessToken(),
                $this->apiDatabaseConfig->expiresInColumnName => $user->getExpiresIn(),
                $this->apiDatabaseConfig->applicationTokenColumnName => $user->getApplicationToken(),
                $this->apiDatabaseConfig->refreshTokenColumnName => $user->getRefreshToken(),
                $this->apiDatabaseConfig->domainColumnName => $user->getDomain(),
                $this->apiDatabaseConfig->clientEndpointColumnName => $user->getClientEndpoint(),
                $this->apiDatabaseConfig->clientIdColumnName => $user->getClientId(),
                $this->apiDatabaseConfig->clientSecretColumnName => $user->getClientSecret(),
            ])
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->bindValue('memberId', $user->getMemberId());

        $stmt = $this->apiDatabaseConfig->pdo->prepare($update->getStatement());
        return $stmt->execute($update->getBindValues());
    }

    public function getUserByMemberId(string $memberId): null|User
    {
        $select = $this->queryFactory->newSelect();
        $select
            ->cols(['*'])
            ->from($this->apiDatabaseConfig->tableName)
            ->where($this->apiDatabaseConfig->memberIdColumnName . "= :memberId")
            ->bindValue('memberId', $memberId);

        $stmt = $this->apiDatabaseConfig->pdo->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $result) {
            return (new User())
                ->setMemberId($memberId)
                ->setAccessToken('')
                ->setExpiresIn('')
                ->setApplicationToken('')
                ->setRefreshToken('')
                ->setDomain('')
                ->setClientEndpoint('')
                ->setClientId('')
                ->setClientSecret('');
        }

        return (new User($result[$this->apiDatabaseConfig->primaryKeyColumnName]))
            ->setMemberId($result[$this->apiDatabaseConfig->memberIdColumnName])
            ->setAccessToken($result[$this->apiDatabaseConfig->accessTokenColumnName])
            ->setExpiresIn($result[$this->apiDatabaseConfig->expiresInColumnName])
            ->setApplicationToken($result[$this->apiDatabaseConfig->applicationTokenColumnName])
            ->setRefreshToken($result[$this->apiDatabaseConfig->refreshTokenColumnName])
            ->setDomain($result[$this->apiDatabaseConfig->domainColumnName])
            ->setClientEndpoint($result[$this->apiDatabaseConfig->clientEndpointColumnName])
            ->setClientId($result[$this->apiDatabaseConfig->clientIdColumnName])
            ->setClientSecret($result[$this->apiDatabaseConfig->clientSecretColumnName]);
    }

    public function save(User $user): bool|string
    {
        if ($this->isUserNotFoundByMemberId($user->getMemberId())) {
            return $this->insert($user);
        } else {
            return $this->update($user);
        }
    }

    private function isUserNotFoundByMemberId(string $memberId): bool
    {
        $user = $this->getUserByMemberId($memberId);

        if (empty($user->getIdPrimaryKey())) {
            return true;
        }

        return false;
    }

}
