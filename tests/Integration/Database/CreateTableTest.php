<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Integration\Database;

use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\DatabaseCore\TableManager;
use SimpleApiBitrix24\Tests\BaseTestCase;

class CreateTableTest extends BaseTestCase
{

    public function test_table_manager_mysql()
    {
        $pdo = $this->createPdo('mysql');
        $databaseConfig = ApiDatabaseConfig::build($pdo, $_ENV['TEST_TABLE_NAME']);

        $tableManager = new TableManager($databaseConfig);
        $tableManager->createUsersTableIfNotExists();

        $sql = "SHOW TABLES LIKE '" . $_ENV['TEST_TABLE_NAME'] . "'" ;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $tableName = $stmt->fetchAll(\PDO::FETCH_COLUMN)[0] ?? null;

        $this->assertTrue($tableName === $_ENV['TEST_TABLE_NAME']);
        $this->dropTable($databaseConfig->pdo, $_ENV['TEST_TABLE_NAME']);
    }

    public function test_table_manager_pgsql()
    {
        $pdo = $this->createPdo('pgsql');
        $databaseConfig = ApiDatabaseConfig::build($pdo, $_ENV['TEST_TABLE_NAME']);

        $tableManager = new TableManager($databaseConfig);
        $tableManager->createUsersTableIfNotExists();

        $sql = "SELECT to_regclass('public." . $_ENV['TEST_TABLE_NAME'] . "')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $tableName = $stmt->fetchColumn();

        $this->assertSame($tableName, $_ENV['TEST_TABLE_NAME']);
        $this->dropTable($databaseConfig->pdo, $_ENV['TEST_TABLE_NAME']);
    }

    public function test_table_manager_sqlite()
    {
        $pdo = $this->createPdo('sqlite');
        $databaseConfig = ApiDatabaseConfig::build($pdo, $_ENV['TEST_TABLE_NAME']);

        $tableManager = new TableManager($databaseConfig);
        $tableManager->createUsersTableIfNotExists();

        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $_ENV['TEST_TABLE_NAME']]);
        $tableName = $stmt->fetchColumn();
        $stmt->closeCursor();

        $this->assertSame($tableName, $_ENV['TEST_TABLE_NAME']);
        $this->dropTable($databaseConfig->pdo, $_ENV['TEST_TABLE_NAME']);
    }
}
