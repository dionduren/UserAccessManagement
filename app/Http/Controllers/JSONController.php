<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JSONService;

class JSONController extends Controller
{
    protected $jsonService;

    public function __construct(JSONService $jsonService)
    {
        $this->jsonService = $jsonService;
    }

    public function regenerateJson()
    {
        $filePath = $this->jsonService->generateMasterDataJson();

        return redirect()->back()->with('success', 'JSON file has been regenerated successfully!');
    }
}
