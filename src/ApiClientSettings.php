<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;


use SimpleApiBitrix24\Connectors\Handlers\OperationTimeLimitHandler;
use SimpleApiBitrix24\Connectors\Handlers\QueryLimitExceededHandler;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\Enums\AuthType;

class ApiClientSettings
{
    private bool $webhookAuthEnabled;
    private bool $tokenAuthEnabled;
    private Webhook|User|null $defaultCredentials = null;
    private ?QueryLimitExceededHandler $queryLimitExceededHandler = null;
    private ?OperationTimeLimitHandler $operationTimeLimitHandler = null;

    public function __construct(AuthType $authType)
    {
        $this->webhookAuthEnabled = $authType === AuthType::WEBHOOK;
        $this->tokenAuthEnabled = $authType === AuthType::TOKEN;
    }

    public function setDefaultCredentials(Webhook|User $credentials): ApiClientSettings
    {
        $this->defaultCredentials = $credentials;
        return $this;
    }

    public function setQueryLimitExceededHandler(
        bool $handleEnabled,
        int $usleep = QueryLimitExceededHandler::USLEEP_DEFAULT
    ): ApiClientSettings {
        $this->queryLimitExceededHandler = new QueryLimitExceededHandler($handleEnabled, $usleep);
        return $this;
    }

    public function setOperationTimeLimitHandler(
        bool $handleEnabled,
        int $usleep = OperationTimeLimitHandler::USLEEP_DEFAULT
    ): ApiClientSettings {
        $this->operationTimeLimitHandler = new OperationTimeLimitHandler($handleEnabled, $usleep);
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

    public function getDefaultCredentials(): Webhook|User|null
    {
        if ($this->webhookAuthEnabled && $this->defaultCredentials === null) {
            return new Webhook('null');
        }

        return $this->defaultCredentials;
    }

    public function getQueryLimitExceededHandler(): QueryLimitExceededHandler
    {
        if (null === $this->queryLimitExceededHandler) {
            $this->queryLimitExceededHandler = new QueryLimitExceededHandler(true);
        }

        return $this->queryLimitExceededHandler;
    }

    public function getOperationTimeLimitHandler(): OperationTimeLimitHandler
    {
        if (null === $this->operationTimeLimitHandler) {
            $this->operationTimeLimitHandler = new OperationTimeLimitHandler(true);
        }

        return $this->operationTimeLimitHandler;
    }
}
