<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdvboxService;
use App\Models\Company;

class TestAdvboxIntegration extends Command
{
    protected $signature = 'advbox:test {--protocol= : Protocolo para testar}';
    protected $description = 'Testa a integração com a API do AdvBox';

    public function handle()
    {
        $this->info('=== Testando Integração com AdvBox ===');
        
        // Buscar empresa com integração AdvBox
        $company = Company::where('advbox_integration_enabled', true)->first();
        
        if (!$company) {
            $this->error('Nenhuma empresa com integração AdvBox encontrada');
            return 1;
        }

        $this->info("Empresa: {$company->name}");
        $this->info("API Key: " . substr($company->advbox_api_key, 0, 10) . "...");

        $advboxService = new AdvboxService($company->advbox_api_key);

        // 1. Testar configurações
        $this->info('1. Testando configurações da API...');
        $settings = $advboxService->getSettings();
        
        if ($settings['success']) {
            $this->info('✓ Configurações obtidas com sucesso');
        } else {
            $this->error('✗ Erro ao obter configurações: ' . $settings['error']);
            return 1;
        }

        // 2. Testar busca de usuários
        $this->info('2. Testando busca de usuários via settings...');
        $users = $advboxService->getUsers();
        
        if ($users['success']) {
            $data = $users['data'];
            $this->info("✓ Usuários encontrados: " . count($data) . " usuário(s)");
            
            if (!empty($data)) {
                $this->table(['ID', 'Nome', 'Email'], array_map(function($user) {
                    return [
                        $user['id'] ?? 'N/A',
                        $user['name'] ?? 'N/A',
                        $user['email'] ?? 'N/A'
                    ];
                }, array_slice($data, 0, 5))); // Mostrar apenas os primeiros 5
            }
        } else {
            $this->error('✗ Erro ao buscar usuários: ' . $users['error']);
        }

        // 3. Testar busca de tarefas
        $this->info('3. Testando busca de tarefas via settings...');
        $tasks = $advboxService->getTasks();
        
        if ($tasks['success']) {
            $data = $tasks['data'];
            $this->info("✓ Tarefas encontradas: " . count($data) . " tarefa(s)");
            
            if (!empty($data)) {
                // Mostrar a estrutura real da primeira tarefa
                $this->info("Estrutura da primeira tarefa:");
                $this->line(json_encode($data[0], JSON_PRETTY_PRINT));
                
                $this->table(['ID', 'Título', 'Descrição'], array_map(function($task) {
                    return [
                        $task['id'] ?? 'N/A',
                        $task['title'] ?? $task['type'] ?? $task['name'] ?? 'N/A',
                        substr($task['description'] ?? $task['desc'] ?? 'N/A', 0, 50) . '...'
                    ];
                }, array_slice($data, 0, 5))); // Mostrar apenas as primeiras 5
            }
        } else {
            $this->error('✗ Erro ao buscar tarefas: ' . $tasks['error']);
        }

        // 4. Testar busca de processo
        $protocol = $this->option('protocol');
        
        if ($protocol) {
            $this->info("4. Testando busca de processo: {$protocol}");
            $lawsuit = $advboxService->searchLawsuit($protocol);
            
            if ($lawsuit['success']) {
                $data = $lawsuit['data'];
                $this->info("✓ Processo encontrado: " . count($data) . " resultado(s)");
                
                if (!empty($data)) {
                    $this->table(['ID', 'Protocolo', 'Cliente'], [
                        [
                            $data[0]['id'] ?? 'N/A',
                            $data[0]['protocol_number'] ?? 'N/A',
                            $data[0]['client_name'] ?? 'N/A'
                        ]
                    ]);
                    
                    // 5. Testar criação de tarefa
                    $this->info('5. Testando criação de tarefa...');
                    $taskResult = $advboxService->createTaskByProtocol($protocol, [
                        'comments' => 'Teste de integração - ' . now()->format('d/m/Y H:i:s'),
                        'urgent' => false,
                        'important' => true,
                        'display_schedule' => true,
                        'folder' => 'Testes'
                    ]);
                    
                    if ($taskResult['success']) {
                        $this->info('✓ Tarefa criada com sucesso');
                        $this->info('ID da tarefa: ' . ($taskResult['data']['id'] ?? 'N/A'));
                    } else {
                        $this->error('✗ Erro ao criar tarefa: ' . $taskResult['error']);
                    }
                }
            } else {
                $this->error('✗ Erro ao buscar processo: ' . $lawsuit['error']);
            }
        } else {
            $this->warn('Use --protocol=NUMERO para testar busca de processo específico');
        }

        $this->info('=== Teste concluído ===');
        return 0;
    }
} 