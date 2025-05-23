<?php

declare(strict_types=1);

namespace SimpleApiBitrix24;

use Monolog\Logger;
use SimpleApiBitrix24\Exceptions\ConnectorException;;
use SimpleApiBitrix24\Connectors\ConnectorFactory;
use SimpleApiBitrix24\Connectors\ConnectorInterface;
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

    /**
     * @throws Throwable
     *
     * @example
     * ```php
     * $api->call('crm.deal.get', ['id' => 2]);
     * ```
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
     * Use this method to change the connection to the Bitrix24 API.
     * Alternatively, use it when you need a second ApiClient instance, e.g.: 
     * ```php
     * $secondApi = cloned $firstApi; $secondApi->ConnectTo('memberId');
     * ```
     * 
     * @param string $webhookOrMemberId Webhook url or member id for connection
     * @return void
     * @throws ConnectorException
     */
    public function connectTo(string $webhookOrMemberId): void
    {
        $this->apiSettings->setDefaultConnection($webhookOrMemberId);
        $this->connector = ConnectorFactory::create($this->apiSettings, $this->apiDatabaseConfig);
    }

}
