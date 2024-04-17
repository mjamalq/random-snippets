<?php

namespace App\Console\Commands;

use App\Models\Odin\IndustryService;
use App\Repositories\Odin\AvailableLeadsRepository;
use App\Repositories\Odin\IndustryServiceRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class CalculateAvailableLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate-available-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates available leads against each industry service and updates the available leads table.';

    /**
     * Execute the console command.
     */
    public function handle(IndustryServiceRepository $serviceRepository, AvailableLeadsRepository $leadsRepository)
    {
        ini_set('memory_limit','-1');

        $this->info("Initiating the processing.");
        $leadsRepository->truncate();

        /** @var IndustryService $services */
        $services = $serviceRepository->getAllServices();
        $period   = Carbon::now()->subDay()->startOfDay()->toDateTimeString();

        foreach ($services as $service) {
            $data = $leadsRepository->getAvailableLeadsForIndustryService($service->{IndustryService::FIELD_ID}, $period)->toArray();
            if(empty($data)) continue;

            $leadChunks = array_chunk($data, 500);
            foreach ($leadChunks as $leadChunk) {
                try
                {
                    $leadsRepository->saveChunk($leadChunk, $period);
                } catch (Exception $exc) {
                    $this->alert("Error: {$exc->getMessage()}");
                }
            }
        }

        $this->info("Finished");
    }
}
