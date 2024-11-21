<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\JSONService;

class MasterDataJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:master-data-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JSON file with company, kompartemen, departemen, and job role data';

    protected $jsonService;

    public function __construct(JSONService $jsonService)
    {
        parent::__construct();
        $this->jsonService = $jsonService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Master Data JSON...');

        // Call the service to generate the JSON
        $this->jsonService->generateMasterDataJson();

        $this->info('Master Data JSON generated successfully.');
    }
}
