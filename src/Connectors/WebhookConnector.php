<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Connectors;

use GuzzleHttp\Client;
use SimpleApiBitrix24\Connectors\Handlers\Dto\ErrorContext;
use SimpleApiBitrix24\Connectors\Interfaces\ConnectorInterface;
use SimpleApiBitrix24\Connectors\Managers\ErrorResponseManager;
use SimpleApiBitrix24\Connectors\Models\Webhook;
use SimpleApiBitrix24\Connectors\Traits\ConnectorTrait;
use SimpleApiBitrix24\Exceptions\ConnectorException;

class WebhookConnector implements ConnectorInterface
{
    use ConnectorTrait;

    private Webhook $webhook;
    private ErrorResponseManager $errorResponseManager;
    private Client $httpClient;

    public function __construct(Webhook $webhook, ErrorResponseManager $errorResponseManager)
    {
        $this->webhook = $webhook;
        $this->errorResponseManager = $errorResponseManager;
        $this->httpClient = new Client();
    }

    /**
     * @throws ConnectorException
     */
    public function sendRequest(string $method, array $params): array
    {
        $this->assertValidCredentials($this->webhook->getUrl());

        $url = $this->webhook->getUrl() . $method . ".json";
        $response = $this->makeHttpRequest($url, $params);
        $response = json_decode($response->getBody()->getContents(), true);

        if ($this->errorResponseManager->shouldTheRequestBeRepeated(new ErrorContext($response))) {
            return $this->sendRequest($method, $params);
        }

        return $response;
    }

    /**
     * @throws ConnectorException
     */
    public function sendBatchRequest(array $queries): array
    {
        $this->assertValidCredentials($this->webhook->getUrl());

        $url = $this->webhook->getUrl() . "batch.json";
        $httpQuery = $this->buildBatchQueries($queries);
        $data = ['cmd' => $httpQuery, 'halt' => 0];

        $response = $this->makeHttpRequest($url, $data);
        $response = json_decode($response->getBody()->getContents(), true);

        if ($this->errorResponseManager->shouldTheRequestBeRepeated(new ErrorContext($response))) {
            return $this->sendBatchRequest($queries);
        }

        return $response;
    }

    /**
     * @throws ConnectorException
     */
    private function assertValidCredentials($webhookUrl): bool
    {
        if (filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return true;
        }

        throw new ConnectorException("the webhook is incorrect: '$webhookUrl'");
    }
}
