<?php

namespace App\Observers;

use App\Services\JSONService;

class MasterDataObserver
{
    protected $jsonService;

    public function __construct(JSONService $jsonService)
    {
        $this->jsonService = $jsonService;
    }

    public function created($model)
    {
        $this->jsonService->generateMasterDataJson();
    }

    public function updated($model)
    {
        $this->jsonService->generateMasterDataJson();
    }

    public function deleted($model)
    {
        $this->jsonService->generateMasterDataJson();
    }
}
