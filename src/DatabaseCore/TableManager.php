<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\DatabaseCore;

use PDO;
use SimpleApiBitrix24\ApiDatabaseConfig;

class TableManager
{
    private ApiDatabaseConfig $databaseConfig;

    public function __construct(ApiDatabaseConfig $databaseConfig)
    {
        $this->databaseConfig = $databaseConfig;
    }

    public function createUsersTableIfNotExists(): bool
    {
        return match($this->databaseConfig->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            'mysql' => $this->mysqlCreateUsersTableIfNotExists(),
            'pgsql' => $this->postgresCreateUsersTableIfNotExists(),
            'sqlite' => $this->sqliteCreateUsersTableIfNotExists(),
            'default' => false
        };
    }

    private function mysqlCreateUsersTableIfNotExists(): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->databaseConfig->tableName} (
                {$this->databaseConfig->userIdColumnName} BIGINT UNSIGNED NOT NULL,
                {$this->databaseConfig->memberIdColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->isAdminColumnName} BOOLEAN NOT NULL DEFAULT 0,
                {$this->databaseConfig->authTokenColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->refreshTokenColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->domainColumnName} VARCHAR(200) NOT NULL,
                {$this->databaseConfig->clientIdColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->clientSecretColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->createdAtColumnName} timestamp NOT NULL,
                {$this->databaseConfig->updatedAtColumnName} timestamp NOT NULL,
                PRIMARY KEY ({$this->databaseConfig->userIdColumnName}, {$this->databaseConfig->memberIdColumnName}),
                INDEX `idx_member_id` ({$this->databaseConfig->memberIdColumnName}),
                INDEX `idx_user_id` ({$this->databaseConfig->userIdColumnName})
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $stmt = $this->databaseConfig->pdo->prepare($sql);
        return $stmt->execute();
    }

    private function sqliteCreateUsersTableIfNotExists(): bool
    {
        $table = $this->databaseConfig->tableName;
        $sql = "
            CREATE TABLE IF NOT EXISTS {$table} (
                {$this->databaseConfig->userIdColumnName} INTEGER NOT NULL,
                {$this->databaseConfig->memberIdColumnName} TEXT NOT NULL,
                {$this->databaseConfig->isAdminColumnName} INTEGER NOT NULL DEFAULT 0,
                {$this->databaseConfig->authTokenColumnName} TEXT NOT NULL,
                {$this->databaseConfig->refreshTokenColumnName} TEXT NOT NULL,
                {$this->databaseConfig->domainColumnName} TEXT NOT NULL,
                {$this->databaseConfig->clientIdColumnName} TEXT NOT NULL,
                {$this->databaseConfig->clientSecretColumnName} TEXT NOT NULL,
                {$this->databaseConfig->createdAtColumnName} TEXT NOT NULL,
                {$this->databaseConfig->updatedAtColumnName} TEXT NOT NULL,
                PRIMARY KEY ({$this->databaseConfig->userIdColumnName}, {$this->databaseConfig->memberIdColumnName})
            );

            CREATE INDEX IF NOT EXISTS idx_member_id ON {$table} ({$this->databaseConfig->memberIdColumnName});
            CREATE INDEX IF NOT EXISTS idx_user_id ON {$table} ({$this->databaseConfig->userIdColumnName});
        ";

        $stmt = $this->databaseConfig->pdo->prepare($sql);
        return $stmt->execute();
    }


    private function postgresCreateUsersTableIfNotExists(): bool
    {
        $table = $this->databaseConfig->tableName;
        $sqlCreateTable = "
            CREATE TABLE IF NOT EXISTS {$table} (
                {$this->databaseConfig->userIdColumnName} BIGINT NOT NULL,
                {$this->databaseConfig->memberIdColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->isAdminColumnName} BOOLEAN NOT NULL DEFAULT FALSE,
                {$this->databaseConfig->authTokenColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->refreshTokenColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->domainColumnName} VARCHAR(200) NOT NULL,
                {$this->databaseConfig->clientIdColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->clientSecretColumnName} VARCHAR(100) NOT NULL,
                {$this->databaseConfig->createdAtColumnName} TIMESTAMP NOT NULL,
                {$this->databaseConfig->updatedAtColumnName} TIMESTAMP NOT NULL,
                PRIMARY KEY ({$this->databaseConfig->userIdColumnName}, {$this->databaseConfig->memberIdColumnName})
            );
        ";

        $sqlCreateMemberIdIndex = "CREATE INDEX IF NOT EXISTS idx_member_id ON {$table} ({$this->databaseConfig->memberIdColumnName})";
        $sqlCreateUserIdIndex = "CREATE INDEX IF NOT EXISTS idx_user_id ON {$table} ({$this->databaseConfig->userIdColumnName})";

        $result = true;

        if ($this->databaseConfig->pdo->exec($sqlCreateTable) === false ||
        $this->databaseConfig->pdo->exec($sqlCreateMemberIdIndex) === false ||
        $this->databaseConfig->pdo->exec($sqlCreateUserIdIndex) === false) {
            $result = false;
        }

        return $result;
    }
}
