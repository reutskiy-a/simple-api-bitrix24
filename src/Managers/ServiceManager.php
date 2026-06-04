<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Managers;

use SimpleApiBitrix24\ApiClientBitrix24;
use SimpleApiBitrix24\ApiDatabaseConfig;
use SimpleApiBitrix24\Services\Batch;
use SimpleApiBitrix24\Services\Installation\InstallationService;

class ServiceManager
{
    private ?Batch $batch = null;
    private ?InstallationService $installationService = null;

    public function __construct(
        private ApiClientBitrix24 $api,
        private ApiDatabaseConfig $apiDatabaseConfig
    ) {

    }

    public function batch(): Batch
    {
        return $this->batch ??= new Batch($this->api);
    }

    public function installation(): InstallationService
    {
        return $this->installationService ??= new InstallationService($this->apiDatabaseConfig);
    }

}
