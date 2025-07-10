<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FeaturesController extends Controller
{
    public function index()
    {
        $features = [
            [
                'id' => 1,
                'title' => 'Inteiro teor dos despachos do INSS',
                'description' => 'Implementação da funcionalidade para visualizar o inteiro teor dos despachos do INSS',
                'deadline' => '2025-08-31',
                'status' => 'in_progress',
                'priority' => 'high'
            ],
            [
                'id' => 2,
                'title' => 'IA com análise preditiva de casos',
                'description' => 'Sistema de inteligência artificial para análise preditiva de casos jurídicos',
                'deadline' => '2025-08-31',
                'status' => 'in_progress',
                'priority' => 'high'
            ],
            [
                'id' => 3,
                'title' => 'Templates de workflows e petições',
                'description' => 'Criação de templates personalizáveis para workflows e petições',
                'deadline' => '2025-08-31',
                'status' => 'in_progress',
                'priority' => 'medium'
            ],
            [
                'id' => 4,
                'title' => 'Integração com API oficial do ADVBOX',
                'description' => 'Integração completa com a API oficial do ADVBOX para sincronização de dados',
                'deadline' => null,
                'status' => 'planned',
                'priority' => 'medium'
            ],
            [
                'id' => 5,
                'title' => 'Melhora na janela de contexto do Chat',
                'description' => 'Aprimoramento da interface e funcionalidades do Chat IA',
                'deadline' => null,
                'status' => 'planned',
                'priority' => 'low'
            ]
        ];

        return Inertia::render('Features/Index', [
            'features' => $features
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        // Verificar se o usuário é superadmin
        if (!auth()->user()->hasRole('superadmin')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Aqui você pode implementar a lógica para salvar o status no banco
        // Por enquanto, apenas retornamos sucesso
        return response()->json(['success' => true]);
    }
} 