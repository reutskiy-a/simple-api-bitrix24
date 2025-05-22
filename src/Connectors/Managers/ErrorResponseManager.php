<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Managers;

use SimpleApiBitrix24\Connectors\Services\EmptyResponseService;
use SimpleApiBitrix24\Connectors\Services\OperationTimeLimitService;
use SimpleApiBitrix24\Connectors\Services\QueryLimitExceededService;
use SimpleApiBitrix24\Connectors\Services\RefreshTokenService;
use SimpleApiBitrix24\DatabaseCore\Models\User;

class ErrorResponseManager
{
    private const ERROR_EXPIRED_TOKEN = 'expired_token';

    public function __construct(
        public readonly EmptyResponseService $emptyResponseService,
        public readonly OperationTimeLimitService $operationTimeLimitService,
        public readonly QueryLimitExceededService $queryLimitExceededService,
        public readonly ?RefreshTokenService $refreshTokenService = null,
    ) {

    }

    public function shouldTheRequestBeRepeated($response, ?User $user = null): bool
    {
        if (is_array($response) && empty($response)) {
            return $this->handleEmptyResponse($response);
        }

        if (isset($response['error'])) {
            return $this->handlerErrorKey($response, $user);
        }

        return false;
    }

    private function handleEmptyResponse($response): bool
    {
        if ($this->emptyResponseService->shouldTheRequestBeRepeated($response)) {
            return true;
        }

        return false;
    }

    private function handlerErrorKey($response, ?User $user = null): bool
    {
        if (array_key_exists('error', $response) && $response['error'] == self::ERROR_EXPIRED_TOKEN) {
            $this->refreshTokenService->refreshUserTokens($user);
            return true;
        }

        if ($this->queryLimitExceededService->shouldTheRequestBeRepeated($response)) {
            return true;
        }

        if ($this->operationTimeLimitService->shouldTheRequestBeRepeated($response)) {
            return true;
        }

        return false;
    }

}
