<?php

namespace Structurize\Peppol\Console;

use Illuminate\Console\Command;
use Structurize\Peppol\Models\Company;
use Structurize\Peppol\Services\PeppolService;

class SyncPeppolCompany extends Command
{
    protected $signature = 'peppol:sync-companies';
    protected $description = 'This command synchronizes companies connected with Peppol';

    public function handle(PeppolService $svc): int
    {
        ini_set('memory_limit', '1024M');

        \Session::put('isUpdateProcedure', 1);

        $this->checkCompanies();

        \Session::forget('isUpdateProcedure');
        $this->info('---- DONE -----');

        return self::SUCCESS;
    }

    private function checkCompanies(): void
    {
        $vat_number = config('peppol.table-fields.companies.vat_number', 'vat_number');
        $company_id = config('peppol.table-fields.companies.id', 'id');

        $companies = Company::whereNotNull($vat_number)->get();
        $peppolService = app(PeppolService::class);

        $_ENV['STRUCTURIZE_API_KEY'] = config('peppol.api_key');

        foreach ($companies as $company) {
            try {
                $peppolService->checkIdentifiers($company->{$vat_number}, $company);
            } catch (\Exception $e) {
                \Log::error('Error checking PEPPOL identifiers for company ID ' . $company->{$company_id} . ': ' . $e->getMessage());
            }
        }
    }
}
