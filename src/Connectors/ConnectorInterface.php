<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors;

interface ConnectorInterface
{
    public function sendRequest(string $method, array $params): array;
    public function sendBatchRequest(array $queries): array;
}
