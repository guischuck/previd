<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEmails extends Command
{
    protected $signature = 'email:process';
    protected $description = 'Processa emails do INSS';

    public function handle()
    {
        $this->info('Iniciando processamento de emails do INSS...');
        
        try {
            $emailService = new EmailService();
            $result = $emailService->processEmails();
            
            if ($result) {
                $this->info('Processamento de emails concluído com sucesso!');
            } else {
                $this->error('Erro durante o processamento dos emails.');
            }
        } catch (\Exception $e) {
            Log::error('Erro durante o processamento: ' . $e->getMessage());
            $this->error('Erro durante o processamento: ' . $e->getMessage());
        }
    }
} 