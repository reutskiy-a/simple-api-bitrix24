<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

use PDO;

final class ApiDatabaseConfig
{
    public function __construct(
        public readonly PDO $pdo,
        public readonly string $tableName,
        public readonly string $primaryKeyColumnName,
        public readonly string $memberIdColumnName,
        public readonly string $accessTokenColumnName,
        public readonly string $expiresInColumnName,
        public readonly string $applicationTokenColumnName,
        public readonly string $refreshTokenColumnName,
        public readonly string $domainColumnName,
        public readonly string $clientEndpointColumnName,
        public readonly string $clientIdColumnName,
        public readonly string $clientSecretColumnName,
    ) {

    }

}
