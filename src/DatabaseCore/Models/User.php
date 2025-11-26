<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\DatabaseCore\Models;

use Carbon\CarbonImmutable;

class User
{
    public function __construct(
        private readonly int $userId,
        private readonly string $memberId,
        private bool   $isAdmin,
        private string $authToken,
        private string $refreshToken,
        private readonly string $domain,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly CarbonImmutable $createdAt,
        private CarbonImmutable $updatedAt,
    ) {

    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getMemberId(): string
    {
        return $this->memberId;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): CarbonImmutable
    {
        return $this->updatedAt;
    }

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
        $this->updateTimestamp();
    }

    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
        $this->updateTimestamp();
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
        $this->updateTimestamp();
    }

    private function updateTimestamp(): void
    {
        $this->updatedAt = CarbonImmutable::now();
    }
}
