<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Rest2\Crm\Deal;

use SimpleApiBitrix24\ApiClientBitrix24;

class Deal
{
    public function __construct(
        private ApiClientBitrix24 $api
    ) {

    }

    /**
     * @return array
     * @link https://apidocs.bitrix24.ru/api-reference/crm/deals/crm-deal-add.html
     */
    public function add(array $params = []): array
    {
        return $this->api->call('crm.deal.add', $params);
    }

    public function update() {}

    public function get() {}

    public function list() {}

    public function delete() {}

    public function fields() {}
}
