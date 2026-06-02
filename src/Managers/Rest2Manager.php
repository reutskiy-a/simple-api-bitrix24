<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Managers;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Rest2\Crm\Company\CompanyUserFields;
use SimpleApiBitrix24\Rest2\Crm\Deal\Deal;
use SimpleApiBitrix24\Rest2\Crm\Deal\DealProductRows;

class Rest2Manager
{
    private ?Deal $deal = null;
    private ?DealProductRows $dealProductRows = null;

    /**
     * Companies
     */
    private ?CompanyUserFields $companyUserFields = null;

    public function __construct(
        private ApiClientBitrix24 $api
    ) {

    }

    public function crmDeal(): Deal
    {
        return $this->deal ??= new Deal($this->api);
    }

    public function crmDealProductRows(): DealProductRows
    {
        return $this->dealProductRows ??= new DealProductRows($this->api);
    }

    public function crmDealUserFields()
    {

    }

    public function crmDealContact()
    {

    }

    public function crmDealDetailsConfiguration()
    {

    }

    public function crmCompanyUserFields(): CompanyUserFields
    {
        return $this->companyUserFields ??= new CompanyUserFields($this->api);
    }
}
