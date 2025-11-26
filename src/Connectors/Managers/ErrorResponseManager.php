<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors\Managers;

use SimpleApiBitrix24\Connectors\Handlers\AccessDeniedHandler;
use SimpleApiBitrix24\Connectors\Handlers\DefaultErrorHandler;
use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Handlers\EmptyResponseHandler;
use SimpleApiBitrix24\Connectors\Interfaces\ErrorHandlerInterface;

/**
 * checks every response received from the bitrix24 server for errors
 */
class ErrorResponseManager
{
    private  EmptyResponseHandler $emptyResponseHandler;
    private array $handlers = [];

    public function __construct()
    {
        $this->loadDefaultErrorHandlers();
    }

    public function addErrorHandler(ErrorHandlerInterface $errorHandler): self
    {
        array_unshift($this->handlers, $errorHandler);
        return $this;
    }

    public function shouldTheRequestBeRepeated(ErrorContext $errorContext): bool
    {
        // first, check if the response is empty
        if (empty($errorContext->response)) {
            return $this->emptyResponseHandler->handle($errorContext);
        }

        if (array_key_exists('error', $errorContext->response) &&
            array_key_exists('error_description', $errorContext->response)) {

            foreach ($this->handlers as $handler) {
                if ($handler->canHandle($errorContext)) {
                    return $handler->handle($errorContext);
                };
            }
        }

        return false;
    }

    private function loadDefaultErrorHandlers(): void
    {
        // DefaultErrorHandler must be the last one
        $this->handlers[] =  new AccessDeniedHandler();
        $this->handlers[] =  new DefaultErrorHandler();

        $this->emptyResponseHandler = new EmptyResponseHandler();
    }

}
