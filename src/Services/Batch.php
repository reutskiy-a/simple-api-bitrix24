<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Services;

use SimpleApiBitrix24\ApiClientBitrix24;

/**
 * Service for handling request limits to the Bitrix24 REST API server.
 */
class Batch
{
    private const REQUEST_LIMIT = 50;
    private const RESPONSE_LIMIT = 50;
    private ApiClientBitrix24 $api;

    public function __construct(ApiClientBitrix24 $api)
    {
        $this->api = $api;
    }


    /**
     * Retrieves all items of a specific entity using a list-type method.
     * Works only with list-type methods (e.g., tasks.task.list).
     *
     * Usage example:
     * ```php
     * $batchService = new Batch($apiClient);
     * $tasks = $batchService->getAll('tasks.task.list', ['filter' => ['STATUS' => 5]]);
     * ```
     *
     * @param string $listMethod The list-type method to call (e.g., 'tasks.task.list')
     * @param array $params Optional parameters for the method (e.g., filters, select fields)
     * @return array All items retrieved from the Bitrix24 API.
     *      Returns only the 'result' key data from each response received from the Bitrix24 server.
     */
    public function getAll(string $listMethod, array $params = []): array
    {
        $getData = $this->api->call($listMethod, $params);
        $total = $getData['total'];
        $cycles = ceil($total / self::REQUEST_LIMIT);
        $query = [];
        $start = 0;

        $error = $getData['error'] ?? null;
        if (null !== $error) {
            return $getData;
        }

        if ($total == 0) {
            return [];
        }
        if ($total <= self::RESPONSE_LIMIT) {
            return is_string(array_key_first($getData['result'])) ? reset($getData['result']) : $getData['result'];
        }

        for($i = 0; $i < $cycles; $i ++) {
            $query[$i] = ['method' => $listMethod, 'params' => $params];
            $query[$i]['params']['start'] = $start;
            $start += self::RESPONSE_LIMIT;
        }

        $query = array_chunk($query, self::REQUEST_LIMIT);

        $merge = [];
        foreach($query as $key) {
            $merge = array_merge($merge, $this->api->callBatch($key)['result']['result']);
        }

        $result = [];
        foreach($merge as $key => $value) {
            $result = array_merge($result, is_string(array_key_first($value)) ? reset($value) : $value);
        }

        return $result;
    }

    /**
     * Works similarly to ApiClientBitrix24::callBatch(), but allows processing more than 50 queries by splitting them automatically.
     * Returns only the 'result' key data from each response received from the Bitrix24 server.
     *
     * NOTE: This method is designed to be universal but has not been tested with all possible Bitrix24 REST API methods.
     * Use it at your own risk.
     *
     * Usage example:
     * ```php
     * $queries[] = [
     *     'method' => 'catalog.product.list',
     *     'params' => [
     *         'filter' => [
     *             'iblockId' => 25,
     *             'property1341' => 'some_value'
     *         ],
     *         'select' => ['id', 'iblockId', 'property1341']
     *     ]
     * ];
     * $batchService = new Batch($apiClient);
     * $results = $batchService->call($queries);
     * ```
     *
     * @param array $queries Array of queries, where each query contains 'method' and 'params' keys
     * @return array Processed results from the Bitrix24 API
     */
    public function call(array $queries): array
    {
        $queryChunks = array_chunk($queries, self::REQUEST_LIMIT);

        $merge = [];
        foreach($queryChunks as $key) {
            $batch = $this->api->callBatch($key);
            $merge = array_merge($merge, $batch['result']['result'], $batch['result']['result_error']);
        }

        $result = [];
        foreach($merge as $key => $value) {

            if (is_bool($value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_int($value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_array($value) && key_exists('error', $value)) {
                $result = array_merge($result, [$value]);
                continue;
            }

            if (is_array($value) && ! key_exists('error', $value)) {

                // для списочных методов
                if (is_array(reset($value))
                    && is_string(array_key_first($value))
                    && key(reset($value)) === 0) {

                    foreach (reset($value) as $innerKey) {
                        $result = array_merge($result, [$innerKey]);
                    }
                    continue;
                }

                // для методов возвращающих массив данных обёрнутых в строковый ключ
                if (is_array(reset($value)) && is_string(array_key_first($value))) {
                    $result = array_merge($result, [reset($value)]);
                    continue;
                }

                // для методов возвращающих сразу массив данных
                if (! is_array(reset($value)) && is_string(array_key_first($value))) {
                    $result = array_merge($result, [$value]);
                    continue;
                }
            }
        }

        return $result;
    }
}

