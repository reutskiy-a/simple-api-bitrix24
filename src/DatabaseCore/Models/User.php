<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\DatabaseCore\Models;

class User
{
    private readonly int|null $idPrimaryKey;
    private string $memberId;
    private string $accessToken;
    private string|int $expiresIn;
    private string $applicationToken;
    private string $refreshToken;
    private string $domain;
    private string $clientEndpoint;
    private string $clientId;
    private string $clientSecret;

    public function __construct(int|null $idPrimaryKey = null)
    {
        $this->idPrimaryKey = $idPrimaryKey;
    }

    public function setMemberId(string $memberId): User
    {
        $this->memberId = $memberId;
        return $this;
    }

    public function setAccessToken(string $accessToken): User
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function setExpiresIn(string|int $expiresIn): User
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

    public function setApplicationToken(string $applicationToken): User
    {
        $this->applicationToken = $applicationToken;
        return $this;
    }

    public function setRefreshToken(string $refreshToken): User
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function setDomain(string $domain): User
    {
        $this->domain = $domain;
        return $this;
    }

    public function setClientEndpoint(string $clientEndpoint): User
    {
        $this->clientEndpoint = $clientEndpoint;
        return $this;
    }

    public function setClientId(string $clientId): User
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $clientSecret): User
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function getMemberId(): string
    {
        return $this->memberId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiresIn(): string|int
    {
        return $this->expiresIn;
    }

    public function getApplicationToken(): string
    {
        return $this->applicationToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getClientEndpoint(): string
    {
        return $this->clientEndpoint;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getIdPrimaryKey(): int|null
    {
        return $this->idPrimaryKey;
    }
}
