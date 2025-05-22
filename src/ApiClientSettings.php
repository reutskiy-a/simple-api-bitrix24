<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;


use SimpleApiBitrix24\Connectors\Services\OperationTimeLimitService;
use SimpleApiBitrix24\Connectors\Services\QueryLimitExceededService;

class ApiClientSettings
{
    private bool $webhookAuthEnabled;
    private bool $tokenAuthEnabled;
    private string $defaultConnection = '';
    private ?QueryLimitExceededService $queryLimitExceededService = null;
    private ?OperationTimeLimitService $operationTimeLimitService = null;

    public function setWebhookAuthEnabled(bool $bool): ApiClientSettings
    {
        $this->webhookAuthEnabled = $bool;
        $this->tokenAuthEnabled = !$bool;

        return $this;
    }

    public function setTokenAuthEnabled(bool $bool): ApiClientSettings
    {
        $this->tokenAuthEnabled = $bool;
        $this->webhookAuthEnabled = !$bool;
        return $this;
    }

    public function setDefaultConnection(string $webhookOrMemberId): ApiClientSettings
    {
        $this->defaultConnection = $webhookOrMemberId;
        return $this;
    }

    public function setQueryLimitExceededService(
        bool $handleEnabled,
        int $usleep = QueryLimitExceededService::USLEEP_DEFAULT
    ): ApiClientSettings {
        $this->queryLimitExceededService = new QueryLimitExceededService($handleEnabled, $usleep);
        return $this;
    }

    public function setOperationTimeLimitService(
        bool $handleEnabled,
        int $usleep = OperationTimeLimitService::USLEEP_DEFAULT
    ): ApiClientSettings {
        $this->operationTimeLimitService = new OperationTimeLimitService($handleEnabled, $usleep);
        return $this;
    }

    public function isWebhookAuthEnabled(): bool
    {
        return $this->webhookAuthEnabled;
    }

    public function isTokenAuthEnabled(): bool
    {
        return $this->tokenAuthEnabled;
    }

    public function getDefaultConnection(): string
    {
        return $this->defaultConnection;
    }

    public function getQueryLimitExceededService(): QueryLimitExceededService
    {
        if (null === $this->queryLimitExceededService) {
            $this->queryLimitExceededService = new QueryLimitExceededService();
        }

        return $this->queryLimitExceededService;
    }

    public function getOperationTimeLimitService(): OperationTimeLimitService
    {
        if (null === $this->operationTimeLimitService) {
            $this->operationTimeLimitService = new OperationTimeLimitService();
        }

        return $this->operationTimeLimitService;
    }
}
