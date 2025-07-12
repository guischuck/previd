<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\HistoricoSituacao;
use App\Models\Despacho;
use App\Services\AdvboxService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\Company;

class AndamentoController extends Controller
{
    private $advboxService;

    public function __construct(AdvboxService $advboxService)
    {
        $this->advboxService = $advboxService;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $companyId = $user->company_id;
            
            // Status permitidos com nomenclatura correta
            $statusPermitidos = ['EXIGÊNCIA', 'CONCLUÍDA'];
            
            // Query inicial para histórico de mudanças
            $query = HistoricoSituacao::where('id_empresa', $companyId)
                ->with(['processo', 'company'])
                ->whereHas('processo', function($q) use ($statusPermitidos) {
                    $q->whereIn('situacao', $statusPermitidos);
                })
                ->where('situacao_atual', '!=', 'EM ANÁLISE'); // Não mostrar mudanças para "Em Análise"
            
            // Filtro de busca (busca no processo relacionado)
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('processo', function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('protocolo', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%");
                });
            }
            
            // Filtro de nova situação
            if ($request->filled('nova_situacao')) {
                $situacaoFiltro = $this->normalizarSituacao($request->get('nova_situacao'));
                $query->where('situacao_atual', $situacaoFiltro);
            }
            
            // Filtro de situação anterior
            if ($request->filled('situacao_anterior')) {
                $situacaoFiltro = $this->normalizarSituacao($request->get('situacao_anterior'));
                $query->where('situacao_anterior', $situacaoFiltro);
            }
            
            // Filtro de visualização (visto/não visto)
            $visualizacao = $request->get('visualizacao', 'nao_visto'); // Padrão para não vistos
            if ($visualizacao === 'visto') {
                $query->where('visto', true);
            } elseif ($visualizacao === 'nao_visto') {
                $query->where('visto', false);
            }
            
            // Filtro de período
            if ($request->filled('periodo')) {
                $periodo = $request->get('periodo');
                switch ($periodo) {
                    case 'hoje':
                        $query->whereDate('data_mudanca', today());
                        break;
                    case 'semana':
                        $query->where('data_mudanca', '>=', now()->subWeek());
                        break;
                    case 'mes':
                        $query->where('data_mudanca', '>=', now()->subMonth());
                        break;
                    case 'trimestre':
                        $query->where('data_mudanca', '>=', now()->subMonths(3));
                        break;
                }
            }
            
            // Buscar andamentos paginados
            $andamentos = $query->orderBy('data_mudanca', 'desc')->paginate(15);

            // Adiciona informação de despachos para cada andamento
            $andamentos->through(function ($andamento) {
                $protocoloNormalizado = preg_replace('/[^0-9]/', '', $andamento->processo->protocolo);
                $andamento->despacho = Despacho::whereRaw('REGEXP_REPLACE(protocolo, "[^0-9]", "") = ?', [$protocoloNormalizado])
                    ->latest('data_email')
                    ->first();
                return $andamento;
            });
            
            // Calcular estatísticas
            $stats = $this->getStatsHistorico($companyId);
            
            // Opções para filtros
            $situacaoOptions = HistoricoSituacao::where('id_empresa', $companyId)
                ->distinct()
                ->pluck('situacao_atual')
                ->filter()
                ->sort()
                ->values();
                
            $situacaoAnteriorOptions = HistoricoSituacao::where('id_empresa', $companyId)
                ->whereNotNull('situacao_anterior')
                ->distinct()
                ->pluck('situacao_anterior')
                ->filter()
                ->sort()
                ->values();
            
            return Inertia::render('Andamentos/Index', [
                'andamentos' => $andamentos,
                'stats' => $stats,
                'situacaoOptions' => $situacaoOptions,
                'situacaoAnteriorOptions' => $situacaoAnteriorOptions,
                'filters' => $request->only(['search', 'nova_situacao', 'situacao_anterior', 'visualizacao', 'periodo'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar andamentos: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar andamentos: ' . $e->getMessage());
        }
    }

    public function getDespacho(Request $request, $protocolo)
    {
        try {
            // Normaliza o protocolo removendo caracteres não numéricos
            $protocoloNormalizado = preg_replace('/[^0-9]/', '', $protocolo);

            // Busca o despacho mais recente para o protocolo normalizado
            $despacho = Despacho::whereRaw('REGEXP_REPLACE(protocolo, "[^0-9]", "") = ?', [$protocoloNormalizado])
                ->latest('data_email')
                ->first();

            if (!$despacho) {
                return response()->json([
                    'error' => 'Nenhum despacho encontrado para este protocolo'
                ], 404);
            }

            return response()->json([
                'despacho' => $despacho
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar despacho: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao buscar despacho'
            ], 500);
        }
    }

    private function getStatsHistorico($companyId)
    {
        $mudancasHoje = HistoricoSituacao::where('id_empresa', $companyId)
            ->whereDate('data_mudanca', today())
            ->count();
            
        $mudancasSemana = HistoricoSituacao::where('id_empresa', $companyId)
            ->where('data_mudanca', '>=', now()->subWeek())
            ->count();
            
        $mudancasMes = HistoricoSituacao::where('id_empresa', $companyId)
            ->where('data_mudanca', '>=', now()->subMonth())
            ->count();
            
        $naoVistos = HistoricoSituacao::where('id_empresa', $companyId)
            ->where('visto', false)
            ->count();
            
        $totalMudancas = HistoricoSituacao::where('id_empresa', $companyId)
            ->count();

        return [
            'mudancas_hoje' => $mudancasHoje,
            'mudancas_semana' => $mudancasSemana,
            'mudancas_mes' => $mudancasMes,
            'nao_vistos' => $naoVistos,
            'total_mudancas' => $totalMudancas,
        ];
    }
    
    public function marcarVisto(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $companyId = $user->company_id;
            
            $historico = HistoricoSituacao::where('id_empresa', $companyId)
                ->findOrFail($id);
            
            $historico->update([
                'visto' => true,
                'visto_em' => now(),
            ]);
            
            return back()->with('success', 'Andamento marcado como visto!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao marcar como visto: ' . $e->getMessage());
        }
    }

    public function marcarTodosVistos(Request $request)
    {
        try {
            $user = auth()->user();
            $companyId = $user->company_id;
            $statusPermitidos = ['EM ANÁLISE', 'EXIGÊNCIA', 'CONCLUÍDA'];
            
            $query = HistoricoSituacao::where('id_empresa', $companyId)
                ->where('visto', false)
                ->whereHas('processo', function($q) use ($statusPermitidos) {
                    $q->whereIn('situacao', $statusPermitidos);
                });
            
            // Aplicar mesmos filtros da busca se existirem
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('processo', function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('protocolo', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('nova_situacao')) {
                $situacaoFiltro = $this->normalizarSituacao($request->get('nova_situacao'));
                $query->where('situacao_atual', $situacaoFiltro);
            }
            
            if ($request->filled('periodo')) {
                $periodo = $request->get('periodo');
                switch ($periodo) {
                    case 'hoje':
                        $query->whereDate('data_mudanca', today());
                        break;
                    case 'semana':
                        $query->where('data_mudanca', '>=', now()->subWeek());
                        break;
                    case 'mes':
                        $query->where('data_mudanca', '>=', now()->subMonth());
                        break;
                    case 'trimestre':
                        $query->where('data_mudanca', '>=', now()->subMonths(3));
                        break;
                }
            }
            
            $updated = $query->update([
                'visto' => true,
                'visto_em' => now(),
            ]);
            
            return back()->with('success', "Marcados {$updated} andamentos como vistos!");
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao marcar todos como vistos: ' . $e->getMessage());
        }
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

    public function adicionarAdvbox(Request $request, Andamento $andamento)
    {
        try {
            // Verificar se a integração está configurada
            $company = Company::find(2);
            if (!$company || !$company->advbox_integration_enabled || !$company->advbox_api_key) {
                return response()->json(['error' => 'Integração com AdvBox não está configurada'], 400);
            }

            // Verificar se o protocolo existe
            $protocolNumber = $andamento->processo->protocolo;
            if (!$protocolNumber) {
                return response()->json(['error' => 'Protocolo não encontrado no processo'], 400);
            }

            $advboxService = new AdvboxService($company->advbox_api_key);

            // Usar a nova função que busca o processo pelo protocolo e cria a tarefa
            $response = $advboxService->createTaskByProtocol($protocolNumber, [
                'comments' => $request->comments,
                'start_date' => $request->start_date,
                'start_time' => $request->start_time,
                'end_date' => $request->end_date,
                'end_time' => $request->end_time,
                'date_deadline' => $request->date_deadline,
                'local' => $request->local,
                'urgent' => $request->urgent ?? false,
                'important' => $request->important ?? false,
                'display_schedule' => $request->display_schedule ?? true,
                'folder' => $request->folder,
            ]);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarefa adicionada com sucesso no AdvBox',
                    'data' => $response['data']
                ]);
            }

            return response()->json([
                'error' => $response['error'] ?? 'Erro ao adicionar tarefa no AdvBox'
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Erro ao adicionar tarefa no AdvBox', [
                'message' => $e->getMessage(),
                'protocol' => $andamento->processo->protocolo ?? 'N/A',
                'andamento_id' => $andamento->id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Erro ao adicionar tarefa no AdvBox: ' . $e->getMessage()
            ], 500);
        }
    }
}