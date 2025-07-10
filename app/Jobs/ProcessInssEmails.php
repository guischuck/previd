<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInssEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function handle()
    {
        try {
            Log::info('Iniciando processamento de emails do INSS', [
                'company_id' => $this->company->id
            ]);

            $emailService = new EmailService($this->company);
            $emailService->processEmails();

            Log::info('Processamento de emails do INSS concluído');
        } catch (\Exception $e) {
            Log::error('Erro ao processar emails do INSS: ' . $e->getMessage());
            throw $e;
        }
    }
} 