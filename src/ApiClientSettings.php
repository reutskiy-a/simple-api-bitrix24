<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

class ApiClientSettings
{
    private bool $webhookAuthEnabled;
    private bool $tokenAuthEnabled;
    private string $defaultConnection = '';

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

}
