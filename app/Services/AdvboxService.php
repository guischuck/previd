<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdvboxService
{
    private $baseUrl;
    private $apiKey;

    public function __construct($apiKey = null)
    {
        $this->baseUrl = 'https://app.advbox.com.br/api/v1';
        $this->apiKey = $apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function createTaskByProtocol($protocolNumber, $taskData)
    {
        try {
            if (!$this->apiKey) {
                throw new \Exception('API key não configurada');
            }

            // 1. Primeiro buscar o processo pelo protocolo
            $lawsuitResult = $this->searchLawsuit($protocolNumber);
            
            if (!$lawsuitResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Processo não encontrado no AdvBox: ' . $lawsuitResult['error']
                ];
            }

            $lawsuits = $lawsuitResult['data'];
            
            // Verificar se encontrou algum processo
            if (empty($lawsuits) || !isset($lawsuits[0]['id'])) {
                return [
                    'success' => false,
                    'error' => "Nenhum processo encontrado no AdvBox com protocolo: {$protocolNumber}"
                ];
            }

            // Pegar o ID do primeiro processo encontrado
            $lawsuitId = $lawsuits[0]['id'];
            
            // 2. Criar a tarefa associada ao processo
            $taskData['lawsuits_id'] = $lawsuitId;
            $taskData['protocol_number'] = $protocolNumber;
            $taskData['process_number'] = $protocolNumber;
            
            return $this->createTask($taskData);
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar tarefa por protocolo no AdvBox', [
                'message' => $e->getMessage(),
                'protocol' => $protocolNumber,
                'data' => $taskData
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao criar tarefa: ' . $e->getMessage()
            ];
        }
    }

    public function createTask($data)
    {
        try {
            if (!$this->apiKey) {
                throw new \Exception('API key não configurada');
            }

            // Garantir que todos os campos obrigatórios estejam presentes
            $taskData = array_merge([
                'from' => null,
                'guests' => [],
                'tasks_id' => null,
                'lawsuits_id' => $data['lawsuits_id'] ?? null,
                'comments' => $data['comments'] ?? '',
                'start_date' => now()->format('d/m/Y'),
                'start_time' => now()->format('H:i'),
                'end_date' => null,
                'end_time' => null,
                'date_deadline' => null,
                'local' => null,
                'urgent' => false,
                'important' => false,
                'display_schedule' => true,
                'date' => now()->format('d/m/Y'),
                'folder' => null,
                'protocol_number' => $data['protocol_number'] ?? null,
                'process_number' => $data['process_number'] ?? null
            ], $data);

            Log::info('Criando tarefa no AdvBox', [
                'data' => $taskData
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->post($this->baseUrl . '/posts', $taskData);

            if ($response->successful()) {
                Log::info('Tarefa criada com sucesso no AdvBox', [
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Erro ao criar tarefa no AdvBox', [
                'status' => $response->status(),
                'response' => $response->json(),
                'request_data' => $taskData
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao criar tarefa: ' . ($response->json()['message'] ?? 'Erro desconhecido')
            ];

        } catch (\Exception $e) {
            Log::error('Exceção ao criar tarefa no AdvBox', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao criar tarefa: ' . $e->getMessage()
            ];
        }
    }

    public function searchLawsuit($protocolNumber)
    {
        try {
            if (!$this->apiKey) {
                throw new \Exception('API key não configurada');
            }

            Log::info('Buscando processo no AdvBox', [
                'protocol' => $protocolNumber
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->baseUrl . '/lawsuits', [
                'protocol_number' => $protocolNumber
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Processo encontrado no AdvBox', [
                    'protocol' => $protocolNumber,
                    'found' => count($data),
                    'data' => $data
                ]);

                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            Log::error('Erro ao buscar processo no AdvBox', [
                'status' => $response->status(),
                'response' => $response->json(),
                'protocol' => $protocolNumber
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao buscar processo: ' . ($response->json()['message'] ?? 'Erro desconhecido')
            ];

        } catch (\Exception $e) {
            Log::error('Exceção ao buscar processo no AdvBox', [
                'message' => $e->getMessage(),
                'protocol' => $protocolNumber
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao buscar processo: ' . $e->getMessage()
            ];
        }
    }

    public function getSettings()
    {
        try {
            if (!$this->apiKey) {
                throw new \Exception('API key não configurada');
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->baseUrl . '/settings');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Erro ao obter configurações: ' . ($response->json()['message'] ?? 'Erro desconhecido')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao obter configurações: ' . $e->getMessage()
            ];
        }
    }
} 