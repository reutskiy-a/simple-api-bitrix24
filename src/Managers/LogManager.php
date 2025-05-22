<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Managers;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use SimpleApiBitrix24\Exceptions\ApiClientBitrix24Exception;
use Throwable;

class LogManager
{
    public function __construct(
        private ?Logger $logger
    ) {

    }

    private function isItOff(): bool
    {
        if ($this->logger === null) {
            return true;
        }

        return false;
    }

    public function handleBatchResponseErrors(array $queries, array $response): void
    {
        if ($this->isItOff()) {
            return;
        }

        if (empty($response['result']['result_error'])) {
            $this->debug('Batch request', ['query' => $queries, 'response' => $response]);
        }

        if (! empty($response['result']['result_error'])) {
            $this->warning('The batch request contains errors', ['query' => $queries, 'response' => $response]);
        }
    }

    public function handleResponseError(array $methodAndParams, array $response): void
    {
        if ($this->isItOff()) {
            return;
        }

        if (! isset($response['error'])) {
            $this->debug('Single request', ['query' => $methodAndParams, 'response' => $response]);
        }

        if (isset($response['error'])) {
            $context = ['query' => $methodAndParams, 'response' => $response];
            $this->warning('The single request failed', $context);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        if ($this->isItOff()) {
            return;
        }

        $this->logger->debug($message, ['data' => $context]);
    }

    public function warning(
        string|\Stringable $message,
        array $context = [],
        ?Throwable $exception = null
    ): void {
        if ($this->isItOff()) {
            return;
        }

        $exception = $exception ?? new ApiClientBitrix24Exception();

        $this->logger->warning($message, [
            'data' => $context,
            'trace' => $exception->getTraceAsString()
        ]);
    }

    public function error(
        string|\Stringable $message,
        array $context = [],
        ?Throwable $exception = null
    ): void {
        if ($this->isItOff()) {
            return;
        }

        $exception = $exception ?? new ApiClientBitrix24Exception();

        $this->logger->error($message, [
            'data' => $context,
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
