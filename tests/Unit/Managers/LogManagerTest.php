<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Tests\Unit\Managers;

use PHPUnit\Framework\TestCase;
use SimpleApiBitrix24\Managers\LogManager;

class LogManagerTest extends TestCase
{
    public function testHandleBatchResponseErrorDoesNotThrowExceptionWhenLoggerIsNull(): void
    {
        $logManager = new LogManager(null);
        $queries = [];
        $response = ['result' => ['result_error' => 'some_error']];

        $logManager->handleBatchResponseErrors($queries, $response);

        $this->expectNotToPerformAssertions();
    }

    public function testHandleResponseErrorDoesNotThrowExceptionWhenLoggerIsNull(): void
    {
        $logManager = new LogManager(null);
        $methodAndParams = [];
        $response = ['error' => 'some_error'];

        $logManager->handleResponseError($methodAndParams, $response);

        $this->expectNotToPerformAssertions();
    }

}
