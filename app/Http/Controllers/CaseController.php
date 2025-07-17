<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('Cases index method called');
            
            $user = auth()->user();
            $companyId = $user->company_id;
            
            // Filtrar por empresa do usuário logado
            $query = LegalCase::with(['assignedTo', 'createdBy'])
                ->where('company_id', $companyId);
            
            // Aplicar filtros de busca
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('client_name', 'like', "%{$search}%");
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }
            
            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->get('assigned_to'));
            }
            
            \Log::info('Query built, getting cases for company:', ['company_id' => $companyId]);
            
            $cases = $query->orderBy('created_at', 'desc')->paginate(15);
            
            \Log::info('Cases retrieved successfully', [
                'total' => $cases->total(),
                'current_page' => $cases->currentPage(),
                'per_page' => $cases->perPage(),
                'data_count' => count($cases->items())
            ]);

            $users = User::select('id', 'name')->get();
            $statuses = [
                'pendente' => 'Pendente',
                'em_coleta' => 'Em Coleta',
                'protocolado' => 'Protocolado',
                'concluido' => 'Concluído',
                'arquivado' => 'Arquivado',
            ];

            \Log::info('Rendering Inertia response...');

            return Inertia::render('Cases/Index', [
                'cases' => $cases,
                'users' => $users,
                'statuses' => $statuses,
                'filters' => $request->only(['search', 'status', 'assigned_to']),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in cases index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function create()
    {
        $benefitTypes = $this->getAvailableBenefitTypes();

        return Inertia::render('Cases/Create', [
            'benefitTypes' => $benefitTypes,
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('CaseController@store called');
        \Log::info('Request data:', $request->all());
        \Log::info('Request has vinculos_empregaticios:', ['has' => $request->has('vinculos_empregaticios')]);
        \Log::info('vinculos_empregaticios value:', ['value' => $request->input('vinculos_empregaticios')]);
        \Log::info('vinculos_empregaticios type:', ['type' => gettype($request->input('vinculos_empregaticios'))]);
        \Log::info('vinculos_empregaticios count:', ['count' => is_array($request->input('vinculos_empregaticios')) ? count($request->input('vinculos_empregaticios')) : 'not array']);
        
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_cpf' => 'required|string|max:14',
            'vinculos_empregaticios' => 'nullable|array',
        ]);

        \Log::info('Validated data:', $validated);

        $validated['case_number'] = $this->generateCaseNumber();
        $validated['created_by'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id; // ← GARANTIR COMPANY_ID
        $validated['status'] = 'pendente';

        \Log::info('About to create case with data:', $validated);

        $case = LegalCase::create($validated);

        \Log::info('Case created with ID:', ['id' => $case->id]);

        // Salvar vínculos empregatícios se fornecidos
        if (!empty($request->vinculos_empregaticios)) {
            \Log::info('Saving employment relationships:', $request->vinculos_empregaticios);
            
            foreach ($request->vinculos_empregaticios as $vinculo) {
                \Log::info('Processing vinculo:', $vinculo);
                
                $case->employmentRelationships()->create([
                    'employer_name' => $vinculo['empregador'] ?? '',
                    'employer_cnpj' => $vinculo['cnpj'] ?? '',
                    'start_date' => $this->parseDate($vinculo['data_inicio'] ?? ''),
                    'end_date' => $this->parseDate($vinculo['data_fim'] ?? ''),
                    'salary' => $this->parseSalary($vinculo['salario'] ?? ''),
                    'is_active' => true, // Todos os vínculos começam como PENDENTE (aguardando coleta)
                    'notes' => 'Extraído automaticamente do CNIS',
                ]);
            }
            
            \Log::info('Employment relationships saved successfully');
        } else {
            \Log::info('No employment relationships to save');
        }

        \Log::info('Redirecting to case show page');

        return redirect()->route('cases.vinculos', $case)
            ->with('success', 'Caso criado com sucesso! Vínculos empregatícios extraídos automaticamente.');
    }

    private function parseDate($dateString): ?string
    {
        if (empty($dateString) || $dateString === 'sem data fim') {
            return null;
        }

        // Converte dd/mm/yyyy para yyyy-mm-dd
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            $date = \DateTime::createFromFormat('d/m/Y', $dateString);
            return $date ? $date->format('Y-m-d') : null;
        }

        return null;
    }

    private function parseSalary($salaryString): ?float
    {
        if (empty($salaryString)) {
            return null;
        }

        // Remove caracteres não numéricos exceto vírgula e ponto
        $cleanSalary = preg_replace('/[^\d,.]/', '', $salaryString);
        
        // Converte vírgula para ponto para float
        $cleanSalary = str_replace(',', '.', $cleanSalary);
        
        return (float) $cleanSalary;
    }

    public function show(LegalCase $case)
    {
        // Verificar se o usuário tem acesso ao caso (mesmo da empresa ou super admin)
        if (!auth()->user()->isSuperAdmin() && $case->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso negado a este caso.');
        }

        $case->load([
            'assignedTo',
            'createdBy',
            'inssProcesses',
            'employmentRelationships',
            'documents',
            'petitions',
            'tasks' => function ($query) {
                $query->where('is_workflow_task', true)->orderBy('order', 'asc');
            },
        ]);

        $users = User::select('id', 'name')->get();
        $benefitTypes = $this->getAvailableBenefitTypes();

        return Inertia::render('Cases/Show', [
            'case' => $case,
            'users' => $users,
            'benefitTypes' => $benefitTypes,
        ]);
    }

    public function edit(LegalCase $case)
    {
        \Log::info('CaseController@edit called', [
            'case_id' => $case->id,
            'case_status' => $case->status,
            'case_data' => $case->toArray()
        ]);

        $users = User::select('id', 'name')->get();
        $benefitTypes = $this->getAvailableBenefitTypes();

        return Inertia::render('Cases/Edit', [
            'case' => $case,
            'users' => $users,
            'benefitTypes' => $benefitTypes,
        ]);
    }

    public function update(Request $request, LegalCase $case)
    {
        // Log detalhado da requisição
        \Log::info('CaseController@update called', [
            'method' => $request->method(),
            'url' => $request->url(),
            'path' => $request->path(),
            'case_id' => $case->id,
            'request_data' => $request->all(),
            'benefit_type_in_request' => $request->input('benefit_type'),
            'benefit_type_in_case' => $case->benefit_type,
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'all_input' => $request->all(),
            'input_has_benefit_type' => $request->has('benefit_type'),
            'input_benefit_type' => $request->input('benefit_type')
        ]);
        
        // Log do conteúdo bruto da requisição
        $rawContent = $request->getContent();
        \Log::info('Raw request content:', [
            'content' => $rawContent,
            'content_length' => strlen($rawContent)
        ]);

        // Para atualizações parciais, validar apenas os campos enviados
        $validationRules = [];
        
        // Log de todos os inputs recebidos
        $allInputs = $request->all();
        \Log::info('All request inputs:', $allInputs);
        \Log::info('Request has benefit_type (has):', ['has' => $request->has('benefit_type')]);
        \Log::info('Request benefit_type value (input):', ['value' => $request->input('benefit_type')]);
        \Log::info('Request benefit_type (all()):', ['value' => $request->all('benefit_type')]);
        \Log::info('Request headers:', ['headers' => $request->headers->all()]);
        
        // Obter tipos de benefício disponíveis
        $availableBenefitTypes = $this->getAvailableBenefitTypes();
        \Log::info('Available benefit types:', $availableBenefitTypes);
        
        if ($request->has('client_name')) {
            $validationRules['client_name'] = 'required|string|max:255';
        }
        
        if ($request->has('client_cpf')) {
            $validationRules['client_cpf'] = 'required|string|max:14';
        }
        
        // Sempre tenta validar o benefit_type se estiver presente na requisição
        if ($request->has('benefit_type')) {
            $benefitType = $request->input('benefit_type');
            \Log::info('Processing benefit_type:', [
                'input_value' => $benefitType,
                'is_string' => is_string($benefitType),
                'in_available_types' => in_array($benefitType, array_keys($availableBenefitTypes)),
                'available_types' => array_keys($availableBenefitTypes)
            ]);
            
            // Se o benefit_type estiver vazio, define como null
            if ($benefitType === '') {
                $benefitType = null;
                $request->merge(['benefit_type' => null]);
            }
            
            // Se não for nulo, valida de acordo com os tipos disponíveis
            if ($benefitType !== null) {
                $validationRules['benefit_type'] = [
                    'required',
                    'string',
                    'in:' . implode(',', array_keys($availableBenefitTypes))
                ];
            } else {
                // Permite null
                $validationRules['benefit_type'] = 'nullable';
            }
            
            \Log::info('Benefit type validation rule added:', ['rule' => $validationRules['benefit_type']]);
        }
        
        if ($request->has('status')) {
            $validationRules['status'] = 'required|in:pendente,em_coleta,protocolado,concluido,arquivado';
            \Log::info('Status validation rule added:', ['rule' => $validationRules['status']]);
        }
        
        if ($request->has('description')) {
            $validationRules['description'] = 'nullable|string';
        }
        
        if ($request->has('notes')) {
            $validationRules['notes'] = 'nullable|string';
        }

        \Log::info('Validation rules before validation:', $validationRules);

        try {
            $validated = $request->validate($validationRules);
            \Log::info('Validation passed. Validated data:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;
        }

        // Verificar se o benefit_type está sendo alterado e criar workflow se necessário
        $oldBenefitType = $case->benefit_type;
        $newBenefitType = $validated['benefit_type'] ?? $oldBenefitType;
        
        // Se o novo tipo de benefício for vazio, define como null
        if ($newBenefitType === '') {
            $newBenefitType = null;
            $validated['benefit_type'] = null;
        }
        
        \Log::info('Benefit type update check:', [
            'old_benefit_type' => $oldBenefitType,
            'new_benefit_type' => $newBenefitType,
            'is_different' => $newBenefitType !== $oldBenefitType,
            'validated_data' => $validated
        ]);
        
        // Atualizar o caso primeiro para garantir que o benefit_type seja salvo
        $case->update($validated);
        
        // Verificar se há tarefas de workflow existentes para este caso
        $existingWorkflowTasks = $case->tasks()->where('is_workflow_task', true)->count();
        
        // Criar workflow se:
        // 1. O tipo de benefício mudou para um valor não nulo, OU
        // 2. Um novo tipo está sendo definido e não há tarefas de workflow existentes
        if ($newBenefitType !== null && ($newBenefitType !== $oldBenefitType || $existingWorkflowTasks === 0)) {
            \Log::info('Creating workflow for case', [
                'old_benefit_type' => $oldBenefitType,
                'new_benefit_type' => $newBenefitType,
                'existing_tasks' => $existingWorkflowTasks,
                'reason' => $newBenefitType !== $oldBenefitType ? 'benefit_type_changed' : 'no_existing_tasks'
            ]);
            
            $this->createWorkflowForCase($case, $newBenefitType);
        } else if ($newBenefitType === null && $existingWorkflowTasks > 0) {
            // Se o tipo de benefício foi removido, remover também as tarefas de workflow
            $deletedTasks = $case->tasks()->where('is_workflow_task', true)->delete();
            \Log::info('Removed workflow tasks because benefit type was removed', [
                'case_id' => $case->id,
                'deleted_tasks' => $deletedTasks
            ]);
        }

        \Log::info('Case updated successfully', [
            'case_id' => $case->id,
            'updated_fields' => array_keys($validated),
            'current_benefit_type' => $case->fresh()->benefit_type
        ]);

        return redirect()->route('cases.show', $case)
            ->with('success', 'Caso atualizado com sucesso!');
    }

    private function createWorkflowForCase(LegalCase $case, string $benefitType): void
    {
        \Log::info('Creating workflow for case', [
            'case_id' => $case->id,
            'benefit_type' => $benefitType,
            'company_id' => $case->company_id
        ]);

        // Buscar template de workflow para o tipo de benefício (globais + da empresa)
        $template = \App\Models\WorkflowTemplate::where('benefit_type', $benefitType)
            ->where('is_active', true)
            ->availableForCompany($case->company_id)
            ->orderBy('is_global', 'asc') // Priorizar templates da empresa sobre globais
            ->first();

        if (!$template) {
            \Log::warning('No workflow template found for benefit type', [
                'benefit_type' => $benefitType,
                'company_id' => $case->company_id,
                'available_templates' => \App\Models\WorkflowTemplate::where('is_active', true)
                    ->availableForCompany($case->company_id)
                    ->pluck('benefit_type', 'id')
                    ->toArray()
            ]);
            return;
        }

        \Log::info('Template found, creating tasks', [
            'template_id' => $template->id,
            'template_name' => $template->name,
            'tasks_count' => count($template->tasks)
        ]);

        // Remover tarefas de workflow existentes para este caso
        $deletedTasks = $case->tasks()->where('is_workflow_task', true)->delete();
        \Log::info('Deleted existing workflow tasks', ['deleted_count' => $deletedTasks]);

        // Criar tarefas baseadas no template
        $createdTasks = 0;
        foreach ($template->tasks as $taskTemplate) {
            $task = $case->tasks()->create([
                'workflow_template_id' => $template->id,
                'title' => $taskTemplate['title'],
                'description' => $taskTemplate['description'],
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->addDays(7), // 7 dias para completar cada tarefa
                'assigned_to' => auth()->id(),
                'created_by' => auth()->id(),
                'required_documents' => $taskTemplate['required_documents'] ?? [],
                'order' => $taskTemplate['order'],
                'is_workflow_task' => true,
            ]);
            $createdTasks++;
            
            \Log::info('Task created', [
                'task_id' => $task->id,
                'title' => $task->title,
                'order' => $task->order
            ]);
        }

        \Log::info('Workflow created successfully for case', [
            'case_id' => $case->id,
            'template_id' => $template->id,
            'tasks_created' => $createdTasks
        ]);
    }

    public function destroy(LegalCase $case)
    {
        $case->delete();

        return redirect()->route('cases.index')
            ->with('success', 'Caso excluído com sucesso!');
    }

    public function vinculos(LegalCase $case)
    {
        \Log::info('Vinculos method called for case:', ['case_id' => $case->id, 'case_number' => $case->case_number]);
        $case->load('employmentRelationships');
        \Log::info('Employment relationships loaded:', [
            'count' => $case->employmentRelationships->count(),
            'relationships' => $case->employmentRelationships->toArray()
        ]);

        return Inertia::render('Cases/Vinculos', [
            'case' => array_merge(
                $case->toArray(),
                ['employment_relationships' => $case->employmentRelationships->toArray()]
            ),
        ]);
    }

    private function generateCaseNumber(): string
    {
        $year = date('Y');
        $lastCase = LegalCase::where('case_number', 'like', "CASE-{$year}-%")
            ->orderBy('case_number', 'desc')
            ->first();

        if ($lastCase) {
            $lastNumber = (int) substr($lastCase->case_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf("CASE-%s-%04d", $year, $newNumber);
    }

        public function dashboard()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                \Log::error('Dashboard: Usuário não autenticado');
                return redirect()->route('login');
            }
            
            $isSuperAdmin = $user->role === 'super_admin';
            \Log::info('Dashboard: Usuário ' . $user->email . ' é super admin: ' . ($isSuperAdmin ? 'sim' : 'não'));
            
            // Dashboard do Super Admin
            if ($isSuperAdmin) {
                try {
                    // Estatísticas de empresas
                    $companiesStats = [
                        'total' => \App\Models\Company::count(),
                        'active' => \App\Models\Company::where('is_active', true)->count(),
                    ];
                    
                    // Estatísticas de usuários
                    $usersStats = [
                        'total' => \App\Models\User::count(),
                        'active' => \App\Models\User::where('is_active', true)->count(),
                    ];
                    
                    // Estatísticas de templates de petição
                    $petitionTemplatesStats = [
                        'total' => \App\Models\PetitionTemplate::count() ?? 0,
                        'active' => \App\Models\PetitionTemplate::where('is_active', true)->count() ?? 0,
                    ];
                    
                    // Estatísticas de templates de workflow
                    $workflowTemplatesStats = [
                        'total' => \App\Models\WorkflowTemplate::count() ?? 0,
                        'active' => \App\Models\WorkflowTemplate::where('is_active', true)->count() ?? 0,
                    ];
                    
                    // Dados financeiros
                    $financial = [
                        'monthly_revenue' => \App\Models\Payment::where('created_at', '>=', now()->startOfMonth())
                            ->where('status', 'paid')
                            ->sum('amount') ?? 0,
                        'recent_payments' => \App\Models\Payment::where('created_at', '>=', now()->subDays(30))
                            ->where('status', 'paid')
                            ->count() ?? 0,
                        'active_subscriptions' => \App\Models\CompanySubscription::where('status', 'active')
                            ->where('current_period_end', '>', now())
                            ->count() ?? 0,
                    ];
                    
                    // Empresas recentes
                    $recentCompanies = \App\Models\Company::with(['users', 'cases'])
                        ->withCount(['users', 'cases'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                    
                    // Atividade recente (7 dias)
                    $recent_activity = [
                        'new_companies' => \App\Models\Company::where('created_at', '>=', now()->subDays(7))->count(),
                        'new_users' => \App\Models\User::where('created_at', '>=', now()->subDays(7))->count(),
                        'recent_payments' => \App\Models\Payment::where('created_at', '>=', now()->subDays(7))
                            ->where('status', 'paid')
                            ->count(),
                    ];
                    
                    return Inertia::render('dashboard', [
                        'isSuperAdmin' => true,
                        'companiesStats' => $companiesStats,
                        'usersStats' => $usersStats,
                        'petitionTemplatesStats' => $petitionTemplatesStats,
                        'workflowTemplatesStats' => $workflowTemplatesStats,
                        'financial' => $financial,
                        'recentCompanies' => $recentCompanies,
                        'recent_activity' => $recent_activity,
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Dashboard Admin - Erro ao carregar estatísticas: ' . $e->getMessage());
                    \Log::error($e->getTraceAsString());
                    
                    // Retornar dashboard com dados mínimos
                    return Inertia::render('dashboard', [
                        'isSuperAdmin' => true,
                        'companiesStats' => ['total' => 0, 'active' => 0],
                        'usersStats' => ['total' => 0, 'active' => 0],
                        'petitionTemplatesStats' => ['total' => 0, 'active' => 0],
                        'workflowTemplatesStats' => ['total' => 0, 'active' => 0],
                        'financial' => [
                            'monthly_revenue' => 0,
                            'recent_payments' => 0,
                            'active_subscriptions' => 0
                        ],
                        'recentCompanies' => [],
                        'recent_activity' => [
                            'new_companies' => 0,
                            'new_users' => 0,
                            'recent_payments' => 0
                        ],
                        'error' => 'Erro ao carregar algumas estatísticas. Por favor, tente novamente.'
                    ]);
                }
            }
            
            // Dashboard do Usuário Normal
            $companyId = $user->company_id;
            
            // Verificar se o usuário tem empresa
            if (!$companyId) {
                \Log::warning('Dashboard: Usuário ' . $user->email . ' não possui empresa associada');
                return Inertia::render('dashboard', [
                    'isSuperAdmin' => false,
                    'stats' => [
                        'total_cases' => 0,
                        'pendente' => 0,
                        'em_coleta' => 0,
                        'protocolado' => 0,
                        'concluido' => 0,
                        'rejeitado' => 0,
                    ],
                    'inssStats' => [
                        'total_processos' => 0,
                        'processos_ativos' => 0,
                        'processos_exigencia' => 0,
                        'processos_concluidos' => 0,
                    ],
                    'recentCases' => [],
                    'casesByStatus' => [],
                    'casesByMonth' => [],
                    'error' => 'Usuário não possui empresa associada.'
                ]);
            }
            
            try {
                // Buscar estatísticas
                $stats = [
                    'total_cases' => \App\Models\LegalCase::where('company_id', $companyId)->count(),
                    'pendente' => \App\Models\LegalCase::where('company_id', $companyId)->where('status', 'pendente')->count(),
                    'em_coleta' => \App\Models\LegalCase::where('company_id', $companyId)->where('status', 'em_coleta')->count(),
                    'protocolado' => \App\Models\LegalCase::where('company_id', $companyId)->where('status', 'protocolado')->count(),
                    'concluido' => \App\Models\LegalCase::where('company_id', $companyId)->where('status', 'concluido')->count(),
                    'rejeitado' => \App\Models\LegalCase::where('company_id', $companyId)->where('status', 'rejeitado')->count(),
                ];

                // Buscar estatísticas dos processos INSS
                $inssStats = [
                    'total_processos' => \App\Models\Processo::where('id_empresa', $companyId)->count(),
                    'processos_ativos' => \App\Models\Processo::where('id_empresa', $companyId)
                        ->where('situacao', 'EM ANÁLISE')
                        ->count(),
                    'processos_exigencia' => \App\Models\Processo::where('id_empresa', $companyId)
                        ->where('situacao', 'EXIGÊNCIA')
                        ->count(),
                    'processos_concluidos' => \App\Models\Processo::where('id_empresa', $companyId)
                        ->where('situacao', 'CONCLUÍDA')
                        ->count(),
                ];

                // Buscar casos recentes
                $recentCases = \App\Models\LegalCase::where('company_id', $companyId)
                    ->with(['assignedTo', 'createdBy'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                // Buscar distribuição por status
                $casesByStatus = \App\Models\LegalCase::where('company_id', $companyId)
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get()
                    ->pluck('total', 'status');
                
                $casesByMonth = \App\Models\LegalCase::where('company_id', $companyId)
                    ->select(
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                        DB::raw('count(*) as total')
                    )
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                return Inertia::render('dashboard', [
                    'isSuperAdmin' => false,
                    'stats' => $stats,
                    'inssStats' => $inssStats,
                    'recentCases' => $recentCases->toArray(),
                    'casesByStatus' => $casesByStatus->toArray(),
                    'casesByMonth' => $casesByMonth->toArray(),
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Dashboard Usuário - Erro ao carregar estatísticas: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                
                return Inertia::render('dashboard', [
                    'isSuperAdmin' => false,
                    'stats' => [
                        'total_cases' => 0,
                        'pendente' => 0,
                        'em_coleta' => 0,
                        'protocolado' => 0,
                        'concluido' => 0,
                        'rejeitado' => 0,
                    ],
                    'inssStats' => [
                        'total_processos' => 0,
                        'processos_ativos' => 0,
                        'processos_exigencia' => 0,
                        'processos_concluidos' => 0,
                    ],
                    'recentCases' => [],
                    'casesByStatus' => [],
                    'casesByMonth' => [],
                    'error' => 'Erro ao carregar estatísticas. Por favor, tente novamente.'
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Dashboard - Erro geral: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return Inertia::render('dashboard', [
                'isSuperAdmin' => false,
                'stats' => [
                    'total_cases' => 0,
                    'pendente' => 0,
                    'em_coleta' => 0,
                    'protocolado' => 0,
                    'concluido' => 0,
                    'rejeitado' => 0,
                ],
                'recentCases' => [],
                'casesByStatus' => [],
                'casesByMonth' => [],
                'error' => 'Erro ao carregar o dashboard. Por favor, tente novamente.'
            ]);
        }
    }

    public function generateCaseDescription(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string',
            'client_cpf' => 'required|string',
            'benefit_type' => 'required|string',
            'vinculos_empregaticios' => 'required|array',
        ]);

        try {
            $vinculos = $request->vinculos_empregaticios;
            $benefitType = $request->benefit_type;
            
            // Calcula tempo total de contribuição
            $totalContribuicao = 0;
            $maiorSalario = 0;
            $empregadores = [];
            
            foreach ($vinculos as $vinculo) {
                $inicio = \DateTime::createFromFormat('d/m/Y', $vinculo['data_inicio']);
                $fim = $vinculo['data_fim'] ? \DateTime::createFromFormat('d/m/Y', $vinculo['data_fim']) : new \DateTime();
                
                if ($inicio && $fim) {
                    $diff = $inicio->diff($fim);
                    $totalContribuicao += $diff->y + ($diff->m / 12) + ($diff->d / 365);
                }
                
                $salario = (float) str_replace(',', '.', $vinculo['salario']);
                if ($salario > $maiorSalario) {
                    $maiorSalario = $salario;
                }
                
                $empregadores[] = $vinculo['empregador'];
            }
            
            $anosContribuicao = round($totalContribuicao, 1);
            $empregadoresUnicos = array_unique($empregadores);
            
            // Gera descrição baseada no tipo de benefício
            $description = "Caso de {$benefitType} para o cliente {$request->client_name} (CPF: {$request->client_cpf}).\n\n";
            $description .= "RESUMO DOS VÍNCULOS EMPREGATÍCIOS:\n";
            $description .= "- Tempo total de contribuição: {$anosContribuicao} anos\n";
            $description .= "- Maior remuneração: R$ " . number_format($maiorSalario, 2, ',', '.') . "\n";
            $description .= "- Empregadores: " . implode(', ', $empregadoresUnicos) . "\n\n";
            
            $description .= "VÍNCULOS DETALHADOS:\n";
            foreach ($vinculos as $index => $vinculo) {
                $description .= ($index + 1) . ". {$vinculo['empregador']}\n";
                $description .= "   Período: {$vinculo['data_inicio']} a " . ($vinculo['data_fim'] ?: 'Atual') . "\n";
                $description .= "   Remuneração: R$ {$vinculo['salario']}\n\n";
            }
            
            // Adiciona análise específica por tipo de benefício
            switch ($benefitType) {
                case 'aposentadoria_por_idade':
                    $description .= "ANÁLISE PARA APOSENTADORIA POR IDADE:\n";
                    $description .= "- Requisito: 65 anos (homem) ou 60 anos (mulher) + 15 anos de contribuição\n";
                    $description .= "- Cliente possui {$anosContribuicao} anos de contribuição\n";
                    break;
                    
                case 'aposentadoria_por_tempo_contribuicao':
                    $description .= "ANÁLISE PARA APOSENTADORIA POR TEMPO DE CONTRIBUIÇÃO:\n";
                    $description .= "- Requisito: 35 anos (homem) ou 30 anos (mulher) de contribuição\n";
                    $description .= "- Cliente possui {$anosContribuicao} anos de contribuição\n";
                    break;
                    
                case 'aposentadoria_professor':
                    $description .= "ANÁLISE PARA APOSENTADORIA DE PROFESSOR:\n";
                    $description .= "- Requisito: 30 anos (homem) ou 25 anos (mulher) de contribuição + exercício em funções de magistério\n";
                    $description .= "- Cliente possui {$anosContribuicao} anos de contribuição\n";
                    break;
                    
                case 'aposentadoria_pcd':
                    $description .= "ANÁLISE PARA APOSENTADORIA DE PCD:\n";
                    $description .= "- Requisito: Deficiência grave + tempo de contribuição variável\n";
                    $description .= "- Cliente possui {$anosContribuicao} anos de contribuição\n";
                    break;
                    
                case 'aposentadoria_especial':
                    $description .= "ANÁLISE PARA APOSENTADORIA ESPECIAL:\n";
                    $description .= "- Requisito: Exposição a agentes nocivos + tempo variável conforme grau de risco\n";
                    $description .= "- Cliente possui {$anosContribuicao} anos de contribuição\n";
                    break;
            }
            
            $description .= "\nPRÓXIMOS PASSOS:\n";
            $description .= "1. Verificar documentação complementar\n";
            $description .= "2. Analisar períodos de interrupção\n";
            $description .= "3. Calcular benefício estimado\n";
            $description .= "4. Preparar petição inicial";
            
            return response()->json([
                'success' => true,
                'description' => $description
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar descrição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function coletas(Request $request)
    {
        $companyId = auth()->user()->company_id;
        // Totais para os cards
        $totalVinculos = \App\Models\EmploymentRelationship::whereHas('legalCase', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count();
        $clientesAtivos = \App\Models\LegalCase::where('company_id', $companyId)
            ->whereHas('employmentRelationships', function($q) {
                $q->where('is_active', true);
            })->count();
        // Clientes finalizados: casos onde TODOS os vínculos foram coletados (is_active = false)
        $clientesFinalizados = \App\Models\LegalCase::where('company_id', $companyId)
            ->whereHas('employmentRelationships') // Tem vínculos
            ->whereDoesntHave('employmentRelationships', function($q) {
                $q->where('is_active', true); // Não tem vínculos ativos (pendentes)
            })->count();
        
        // Empresas pendentes: conta vínculos pendentes (is_active = true)
        $empresasPendentes = \App\Models\EmploymentRelationship::whereHas('legalCase', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('is_active', true)
            ->count();
        
        // Empresas concluídas: conta vínculos concluídos (is_active = false)
        $empresasConcluidas = \App\Models\EmploymentRelationship::whereHas('legalCase', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('is_active', false)
            ->count();
        $coletasAtrasadas = \App\Models\LegalCase::where('company_id', $companyId)
            ->whereHas('employmentRelationships', function($q) {
                $q->where('is_active', true);
            })
            ->where('created_at', '<', now()->subMonths(6))
            ->count();

        // Busca
        $tab = $request->get('tab', 'clientes');
        $search = $request->get('search', '');
        $resultados = [];
        if ($tab === 'clientes' && $search) {
            $resultados = \App\Models\LegalCase::where('company_id', $companyId)
                ->where(function($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                      ->orWhere('client_cpf', 'like', "%{$search}%");
                })
                ->with('employmentRelationships')
                ->get();
        } elseif ($tab === 'empresas' && $search) {
            $resultados = \App\Models\EmploymentRelationship::whereHas('legalCase', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->where('employer_name', 'like', "%{$search}%")
                ->with('legalCase')
                ->get();
            // Forçar serialização correta
            $resultados->each(function($v) { $v->legalCase = $v->legalCase; });
        } elseif ($tab === 'cargos' && $search) {
            $resultados = \App\Models\EmploymentRelationship::whereHas('legalCase', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->where('position', 'like', "%{$search}%")
                ->with('legalCase')
                ->get();
            $resultados->each(function($v) { $v->legalCase = $v->legalCase; });
        }

        return Inertia::render('Coletas', [
            'cards' => [
                'totalVinculos' => $totalVinculos,
                'clientesAtivos' => $clientesAtivos,
                'clientesFinalizados' => $clientesFinalizados,
                'empresasPendentes' => $empresasPendentes,
                'empresasConcluidas' => $empresasConcluidas,
                'coletasAtrasadas' => $coletasAtrasadas,
            ],
            'tab' => $tab,
            'search' => $search,
            'resultados' => $resultados,
        ]);
    }

    /**
     * Busca apenas os tipos de benefício que têm templates de workflow ativos
     */
    private function getAvailableBenefitTypes(): array
    {
        // Mapeamento para nomes amigáveis
        $benefitTypeNames = [
            'aposentadoria_por_idade' => 'Aposentadoria por Idade',
            'aposentadoria_por_tempo_contribuicao' => 'Aposentadoria por Tempo de Contribuição',
            'aposentadoria_professor' => 'Aposentadoria Professor',
            'aposentadoria_pcd' => 'Aposentadoria PCD',
            'aposentadoria_especial' => 'Aposentadoria Especial',
            'aposentadoria_por_invalidez' => 'Aposentadoria por Invalidez',
            'auxilio_doenca' => 'Auxílio-Doença',
            'beneficio_por_incapacidade' => 'Benefício por Incapacidade',
            'pensao_por_morte' => 'Pensão por Morte',
            'auxilio_acidente' => 'Auxílio-Acidente',
            'salario_maternidade' => 'Salário-Maternidade',
            'outro' => 'Outro',
        ];

        // Buscar templates ativos disponíveis para a empresa do usuário
        $userCompanyId = auth()->user()->company_id;
        
        $availableTemplates = \App\Models\WorkflowTemplate::where('is_active', true)
            ->availableForCompany($userCompanyId)
            ->distinct('benefit_type')
            ->pluck('benefit_type')
            ->toArray();

        // Retornar apenas os tipos que têm templates
        $availableBenefitTypes = [];
        foreach ($availableTemplates as $benefitType) {
            if (isset($benefitTypeNames[$benefitType])) {
                $availableBenefitTypes[$benefitType] = $benefitTypeNames[$benefitType];
            }
        }

        return $availableBenefitTypes;
    }

    /**
     * Retorna as tarefas de workflow de um caso específico
     */
    public function getCaseTasks(LegalCase $case)
    {
        try {
            // Log detalhado da requisição
            \Log::info('=== INÍCIO getCaseTasks ===');
            \Log::info('Dados da requisição:', [
                'case_id' => $case->id,
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'headers' => request()->headers->all(),
                'user' => auth()->check() ? [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'is_admin' => auth()->user()->isAdmin()
                ] : 'Usuário não autenticado'
            ]);
            
            \Log::info('Dados do caso:', [
                'case_id' => $case->id,
                'client_name' => $case->client_name,
                'benefit_type' => $case->benefit_type,
                'has_benefit_type' => !empty($case->benefit_type),
                'company_id' => $case->company_id,
                'created_at' => $case->created_at,
                'updated_at' => $case->updated_at,
                'exists' => $case->exists,
                'was_recently_created' => $case->wasRecentlyCreated
            ]);

            // Verificar se o caso foi carregado corretamente
            if (!$case->exists) {
                $error = 'Caso não encontrado';
                \Log::error($error, ['case_id' => $case->id]);
                return response()->json([
                    'success' => false,
                    'error' => $error,
                    'tasks' => []
                ], 404);
            }

            // Verificar permissões do usuário
            $user = auth()->user();
            if (!$user->isAdmin() && $user->company_id !== $case->company_id) {
                $error = 'Acesso não autorizado a este caso';
                \Log::warning($error, [
                    'user_id' => $user->id,
                    'user_company_id' => $user->company_id,
                    'case_company_id' => $case->company_id
                ]);
                return response()->json([
                    'success' => false,
                    'error' => $error,
                    'tasks' => []
                ], 403);
            }

            // Log das tarefas relacionadas
            $allTasks = $case->tasks()->get();
            \Log::info('Todas as tarefas do caso (sem filtro):', [
                'total' => $allTasks->count(),
                'tasks' => $allTasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'is_workflow_task' => $task->is_workflow_task,
                        'order' => $task->order,
                        'status' => $task->status,
                        'due_date' => $task->due_date,
                        'completed_at' => $task->completed_at,
                        'created_at' => $task->created_at,
                        'updated_at' => $task->updated_at
                    ];
                })
            ]);

            // Buscar apenas as tarefas de workflow
            $tasks = $case->tasks()
                ->where('is_workflow_task', true)
                ->orderBy('order', 'asc')
                ->get();

            \Log::info('Tarefas de workflow encontradas:', [
                'total' => $tasks->count(),
                'tasks' => $tasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'is_workflow_task' => $task->is_workflow_task,
                        'order' => $task->order,
                        'status' => $task->status,
                        'due_date' => $task->due_date,
                        'completed_at' => $task->completed_at,
                        'created_at' => $task->created_at,
                        'updated_at' => $task->updated_at
                    ];
                })
            ]);

            $response = [
                'success' => true,
                'tasks' => $tasks,
                'debug' => [
                    'case_id' => $case->id,
                    'benefit_type' => $case->benefit_type,
                    'task_count' => $tasks->count(),
                    'all_task_count' => $allTasks->count(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_admin' => $user->isAdmin()
                    ],
                    'timestamp' => now()->toDateTimeString(),
                    'environment' => config('app.env'),
                    'debug_mode' => config('app.debug')
                ]
            ];

            \Log::info('Resposta da API getCaseTasks:', [
                'success' => $response['success'],
                'task_count' => $response['debug']['task_count'],
                'all_task_count' => $response['debug']['all_task_count'],
                'user_id' => $response['debug']['user']['id']
            ]);
            
            \Log::info('=== FIM getCaseTasks ===');

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao carregar tarefas: ' . $e->getMessage(),
                'tasks' => [],
            ], 500);
        }
    }

    /**
     * Atualiza as anotações de um caso
     */
    public function updateNotes(Request $request, LegalCase $case)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $case->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Anotações atualizadas com sucesso!',
        ]);
    }
}