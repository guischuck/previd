<?php

// ProcessoController.php - Corrigido
namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\HistoricoSituacao;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProcessoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                throw new \Exception('Usuário não logado');
            }
            
            $companyId = $user->company_id;
            if (!$companyId) {
                throw new \Exception('Usuário sem empresa associada');
            }
            
            // Status permitidos com nomenclatura correta
            $statusPermitidos = ['EM ANÁLISE', 'EXIGÊNCIA', 'CONCLUÍDA'];
            
            $query = Processo::with(['company'])
                ->where('id_empresa', $companyId)
                ->whereIn('situacao', $statusPermitidos);
            
            // Aplicar filtros de busca
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('protocolo', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%");
                });
            }
            
            // Filtro de status com conversão
            if ($request->filled('status') && $request->get('status') !== '') {
                $statusFiltro = $this->normalizarSituacao($request->get('status'));
                $query->where('situacao', $statusFiltro);
            }
            
            if ($request->filled('servico')) {
                $query->where('servico', $request->get('servico'));
            }
            
            // Filtro de período
            if ($request->filled('periodo')) {
                $periodo = $request->get('periodo');
                switch ($periodo) {
                    case 'hoje':
                        $query->whereDate('ultima_atualizacao', today());
                        break;
                    case 'semana':
                        $query->where('ultima_atualizacao', '>=', now()->subWeek());
                        break;
                    case 'mes':
                        $query->where('ultima_atualizacao', '>=', now()->subMonth());
                        break;
                    case 'trimestre':
                        $query->where('ultima_atualizacao', '>=', now()->subMonths(3));
                        break;
                }
            }
            
            // Buscar processos paginados
            $processos = $query->orderBy('ultima_atualizacao', 'desc')->paginate(15);
            
            // Calcular estatísticas para os cards
            $stats = $this->getStats($companyId);
            
            // Buscar opções para filtros (apenas status permitidos)
            $statusOptions = Processo::where('id_empresa', $companyId)
                ->whereIn('situacao', $statusPermitidos)
                ->distinct()
                ->pluck('situacao')
                ->filter()
                ->sort()
                ->values();
                
            $servicoOptions = Processo::where('id_empresa', $companyId)
                ->whereIn('situacao', $statusPermitidos)
                ->distinct()
                ->pluck('servico')
                ->filter()
                ->sort()
                ->values();

            return Inertia::render('InssProcesses/Index', [
                'processos' => $processos,
                'stats' => $stats,
                'statusOptions' => $statusOptions,
                'servicoOptions' => $servicoOptions,
                'filters' => $request->only(['search', 'status', 'servico', 'periodo']),
            ]);
            
        } catch (\Exception $e) {
            // Retornar com dados padrão em caso de erro
            return Inertia::render('InssProcesses/Index', [
                'error' => $e->getMessage(),
                'processos' => [
                    'data' => [],
                    'total' => 0,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
                'stats' => [
                    'processos_ativos' => 0,
                    'processos_concluidos' => 0,
                    'processos_exigencia' => 0,
                    'protocolados_hoje' => 0,
                    'total_processos' => 0,
                ],
                'statusOptions' => [],
                'servicoOptions' => [],
                'filters' => [],
            ]);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $processo = Processo::with(['company', 'historicoSituacoes'])
            ->where('id_empresa', $companyId)
            ->findOrFail($id);
            
        return Inertia::render('InssProcesses/Show', [
            'processo' => $processo,
        ]);
    }

    private function getStats($companyId)
    {
        // Status específicos que queremos mostrar
        $statusPermitidos = ['EM ANÁLISE', 'EXIGÊNCIA', 'CONCLUÍDA'];
        
        // Estatísticas baseadas apenas nos status permitidos
        $totalProcessos = Processo::where('id_empresa', $companyId)
            ->whereIn('situacao', $statusPermitidos)
            ->count();
        
        // Processos em análise
        $processosAtivos = Processo::where('id_empresa', $companyId)
            ->where('situacao', 'EM ANÁLISE')
            ->count();
            
        // Processos concluídos
        $processosConcluidos = Processo::where('id_empresa', $companyId)
            ->where('situacao', 'CONCLUÍDA')
            ->count();
            
        // Processos em exigência
        $processosExigencia = Processo::where('id_empresa', $companyId)
            ->where('situacao', 'EXIGÊNCIA')
            ->count();
            
        // Protocolados hoje (apenas dos status permitidos)
        $processosHoje = Processo::where('id_empresa', $companyId)
            ->whereIn('situacao', $statusPermitidos)
            ->whereDate('protocolado_em', today())
            ->count();

        return [
            'processos_ativos' => $processosAtivos,
            'processos_concluidos' => $processosConcluidos,
            'processos_exigencia' => $processosExigencia,
            'protocolados_hoje' => $processosHoje,
            'total_processos' => $totalProcessos,
        ];
    }

    /**
     * Normalizar situação para o formato correto do banco
     */
    private function normalizarSituacao($situacao)
    {
        $situacaoLower = strtolower(trim($situacao));
        
        switch ($situacaoLower) {
            case 'em análise':
            case 'em analise':
            case 'análise':
            case 'analise':
                return 'EM ANÁLISE';
                
            case 'exigência':
            case 'exigencia':
            case 'em exigência':
            case 'em exigencia':
                return 'EXIGÊNCIA';
                
            case 'concluída':
            case 'concluida':
            case 'concluído':
            case 'concluido':
            case 'finalizada':
            case 'finalizado':
                return 'CONCLUÍDA';
                
            default:
                return strtoupper($situacao);
        }
    }
}