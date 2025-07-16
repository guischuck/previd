<?php

namespace App\Http\Controllers;

use App\Services\AdvboxService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class UsuariosController extends Controller
{
    protected $advboxService;

    public function __construct(AdvboxService $advboxService)
    {
        $this->advboxService = $advboxService;
    }

    public function index(Request $request)
    {
        // Inicializa com valores padrão
        $advboxSettings = [
            'success' => false,
            'data' => null,
            'error' => null
        ];

        try {
            // Tenta obter as configurações do AdvBox
            $apiKey = config('services.advbox.api_key');
            
            if ($apiKey) {
                $this->advboxService->setApiKey($apiKey);
                $advboxSettings = $this->advboxService->getSettings();
            } else {
                $advboxSettings['error'] = 'API key do AdvBox não configurada';
                Log::warning('Tentativa de acessar configurações do AdvBox sem API key configurada');
            }
        } catch (\Exception $e) {
            $advboxSettings['error'] = 'Erro ao obter configurações do AdvBox: ' . $e->getMessage();
            Log::error('Erro ao obter configurações do AdvBox', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return Inertia::render('Usuarios/Index', [
            'advboxSettings' => $advboxSettings
        ]);
    }
}
