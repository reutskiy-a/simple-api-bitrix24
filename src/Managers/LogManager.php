<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Managers;

use Psr\Log\LoggerInterface;
use SimpleApiBitrix24\Exceptions\ApiClientBitrix24Exception;
use Throwable;

class LogManager
{
    public function __construct(
        private ?LoggerInterface $logger
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
            $this->debug('Batch request', ['queries' => $queries, 'response' => $response]);
        }

        if (! empty($response['result']['result_error'])) {
            $context = [];
            array_walk($response['result']['result_error'], function($value, $key) use (&$context, $queries) {
                $context[] = ['tried_query' => $queries[$key], 'got_error' => $value];
            });

            $this->warning('The batch request failed', $context);
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
            $context = ['tried_query' => $methodAndParams, 'got_error' => $response];
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
        Throwable $exception = new ApiClientBitrix24Exception()
    ): void {
        if ($this->isItOff()) {
            return;
        }

        $this->logger->warning($message, [
            'data' => $context,
            'trace' => $exception->getTraceAsString()
        ]);
    }

    public function error(
        string|\Stringable $message,
        array $context = [],
        Throwable $exception = new ApiClientBitrix24Exception()
    ): void {
        if ($this->isItOff()) {
            return;
        }

        $this->logger->error($message, [
            'data' => $context,
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
