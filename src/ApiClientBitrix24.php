<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

use Monolog\Logger;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Connectors\Interfaces\ConnectorInterface;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\DatabaseCore\Models\User;
use SimpleApiBitrix24\Exceptions\ConnectorException;
use SimpleApiBitrix24\Managers\LogManager;
use Throwable;

class ApiClientBitrix24
{
    private ApiClientSettings $apiSettings;
    private ?ApiDatabaseConfig $apiDatabaseConfig;
    private LogManager $logManager;
    private ConnectorInterface $connector;

    public function __construct(
        ApiClientSettings $apiSettings,
        ?ApiDatabaseConfig $apiDatabaseConfig = null,
        ?Logger $logger = null
    ) {
        $this->apiSettings = $apiSettings;
        $this->apiDatabaseConfig = $apiDatabaseConfig;
        $this->logManager = new LogManager($logger);
        $this->connector = ConnectorFactory::create($apiSettings, $apiDatabaseConfig);
    }

    public function getCredentials(): Webhook|User|null
    {
        return $this->apiSettings->getDefaultCredentials();
    }

    /**
     * @throws Throwable
     *
     * @example
     * ```php
     * $api->call('crm.deal.get', ['id' => 2]);
     * ```
     * @param string $method
     * @param array $params
     * @return array
     * @throws Throwable
     */
    public function call(string $method, array $params = []): array
    {
        try {
            $response = $this->connector->sendRequest($method, $params);
            $this->logManager->handleResponseError(['method' => $method, 'params' => $params], $response);
            return $response;
        } catch (Throwable $exception) {
            $this->logManager->error($exception->getMessage(), [], $exception);
            throw $exception;
        }
    }

    /**
     * @throws Throwable
     *
     * @example
     * ```php
     * $api->callBatch([
     *     ['method' => 'scope', 'params' => []],
     *     ['method' => 'crm.deal.get', 'params' => ['id' => 2]]
     * ]);
     * ```
     */
    public function callBatch(array $queries): array
    {
        try {
            $response =  $this->connector->sendBatchRequest($queries);
            $this->logManager->handleBatchResponseErrors($queries, $response);
            return $response;
        } catch (Throwable $exception) {
            $this->logManager->error($exception->getMessage(), [], $exception);
            throw $exception;
        }
    }

    /**
     * Updates the authorization credentials used by this ApiClient instance.
     * After calling this method, all subsequent API requests will be executed
     * using the provided Webhook or User credentials.
     *
     * This is useful when you need to switch the active Bitrix24 account
     * or reuse an existing ApiClient instance for another user.
     *
     * Use it when you need a second ApiClient instance, e.g.:
     * ```php
     * $secondApi = cloned $firstApi; $secondApi->setCredentials($credentials);
     * ```
     *
     * @param Webhook|User $credentials
     * @return string
     * @throws ConnectorException
     */
    public function setCredentials(Webhook|User $credentials): string
    {
        $this->apiSettings->setDefaultCredentials($credentials);
        $this->connector = ConnectorFactory::create($this->apiSettings, $this->apiDatabaseConfig);
        return get_class($this->connector);
    }

    public function __clone()
    {
        $this->apiSettings = clone $this->apiSettings;
    }
}
