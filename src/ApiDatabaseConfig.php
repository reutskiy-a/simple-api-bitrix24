<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

use PDO;
use SimpleApiBitrix24\Constants\DatabaseConstants;

final class ApiDatabaseConfig
{

    public function __construct(
        public readonly PDO    $pdo,
        public readonly string $tableName,
        public readonly string $userIdColumnName,               // db type: int (big_int unsigned) NOT NULL
        public readonly string $memberIdColumnName,             // db type: string (100) NOT NULL
        public readonly string $isAdminColumnName,              // db type: bool (1) NOT NULL DEFAULT 0
        public readonly string $authTokenColumnName,            // db type: string (100) NOT NULL
        public readonly string $refreshTokenColumnName,         // db type: string (100) NOT NULL
        public readonly string $domainColumnName,               // db type: string (200) NOT NULL
        public readonly string $clientIdColumnName,             // db type: string (100) NOT NULL
        public readonly string $clientSecretColumnName,         // db type: string (100) NOT NULL
        public readonly string $createdAtColumnName,            // db type: timestamp NOT NULL
        public readonly string $updatedAtColumnName,            // db type: timestamp NOT NULL
    ) {

    }

    /**
     * You can create an object with the table name and default fields suggested by this package.
     * Or specify your own through the constructor.
     *
     * @param PDO $pdo
     * @return ApiDatabaseConfig
     */
    public static function build(PDO $pdo, string|null $tableName = null): ApiDatabaseConfig
    {
        return new ApiDatabaseConfig(
            pdo: $pdo,
            tableName: $tableName ?? DatabaseConstants::TABLE_NAME,
            userIdColumnName: DatabaseConstants::USER_ID_COLUMN_NAME,
            memberIdColumnName: DatabaseConstants::MEMBER_ID_COLUMN_NAME,
            isAdminColumnName: DatabaseConstants::IS_ADMIN_COLUMN_NAME,
            authTokenColumnName: DatabaseConstants::AUTH_TOKEN_COLUMN_NAME,
            refreshTokenColumnName: DatabaseConstants::REFRESH_TOKEN_COLUMN_NAME,
            domainColumnName: DatabaseConstants::DOMAIN_COLUMN_NAME,
            clientIdColumnName: DatabaseConstants::CLIENT_ID_COLUMN_NAME,
            clientSecretColumnName: DatabaseConstants::CLIENT_SECRET_COLUMN_NAME,
            createdAtColumnName: DatabaseConstants::CREATED_AT_COLUMN_NAME,
            updatedAtColumnName: DatabaseConstants::UPDATED_AT_COLUMN_NAME
        );
    }
}
