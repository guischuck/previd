<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessarEmailsInss extends Command
{
    protected $signature = 'emails:processar-inss';
    protected $description = 'Processa emails recebidos do INSS';

    public function handle()
    {
        $this->info('Iniciando processamento de emails do INSS...');

        try {
            // Busca a empresa principal (id = 1)
            $company = Company::find(1);
            
            if (!$company) {
                $this->error('Empresa principal não encontrada');
                return false;
            }

            $emailService = new EmailService($company);
            $emailService->processEmails();

            $this->info('Processamento de emails concluído com sucesso!');
            return true;
        } catch (\Exception $e) {
            Log::error('Erro durante o processamento de emails: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Erro durante o processamento: ' . $e->getMessage());
            return false;
        }
    }
}
