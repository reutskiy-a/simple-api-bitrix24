<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Rest2\Crm\Company;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\DTOs\BatchItem;

class CompanyUserFields
{
    private ?BatchItem $batchItem = null;

    public function __construct(
        private ApiClientBitrix24 $api
    ) {

    }

    public function execute(): array
    {
        return $this->api->call($this->batchItem->method, $this->batchItem->params);
    }

    public function batchItem(): BatchItem
    {
        return $this->batchItem;
    }

    /**
     * @link https://apidocs.bitrix24.ru/api-reference/crm/companies/userfields/crm-company-userfield-add.html
     * @param array $fields
     * @return array
     */
    public function add(array $fields): CompanyUserFields
    {
        $this->batchItem = new BatchItem(
            'crm.company.userfield.add',
            $fields
        );

        return $this;
    }

    /**
     * @link https://apidocs.bitrix24.ru/api-reference/crm/companies/userfields/crm-company-userfield-list.html
     * @param array $filter
     * @param array $order
     * @return array
     */
    public function list(array $filter = [], array $order = []): CompanyUserFields
    {
        $this->batchItem = new BatchItem(
            'crm.company.userfield.list',
            [
                'filter' => $filter,
                'order' => $order
            ]
        );

        return $this;
    }
}
