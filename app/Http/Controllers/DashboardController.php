<?php

namespace App\Http\Controllers;

use App\Models\InssProcess;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas dos casos
        $stats = [
            'total_cases' => LegalCase::count(),
            'pendente' => LegalCase::where('status', 'pendente')->count(),
            'em_coleta' => LegalCase::where('status', 'em_coleta')->count(),
            'protocolado' => LegalCase::where('status', 'protocolado')->count(),
            'concluido' => LegalCase::where('status', 'concluido')->count(),
            'rejeitado' => LegalCase::where('status', 'rejeitado')->count(),
        ];

        // Estatísticas dos processos INSS
        $inssStats = [
            'total_processos' => InssProcess::count(),
            'processos_ativos' => InssProcess::where('status', 'analysis')->count(),
            'processos_exigencia' => InssProcess::where('status', 'requirement')->count(),
            'processos_concluidos' => InssProcess::whereIn('status', ['completed', 'rejected'])->count(),
        ];

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'inssStats' => $inssStats,
        ]);
    }
}