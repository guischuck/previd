<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmploymentRelationshipController;
use App\Http\Controllers\Api\CollectionAttemptController;
use App\Http\Controllers\Api\DeepSeekChatController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CompanyApiController;
use App\Http\Controllers\Api\ProcessoSyncController;
use App\Http\Controllers\PetitionController;
use App\Models\User;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LawsuitController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Endpoint de teste sem autenticação
Route::get('/test-clients', function() {
    return response()->json([
        ['id' => 1, 'name' => 'João Silva Santos'],
        ['id' => 2, 'name' => 'Maria Oliveira Costa'],
        ['id' => 3, 'name' => 'Pedro Fernandes Lima'],
        ['id' => 4, 'name' => 'Ana Paula Rodrigues'],
        ['id' => 5, 'name' => 'Carlos Eduardo Souza']
    ]);
});

// Rotas para o chat que usam auth web ao invés de sanctum
Route::middleware(['auth', 'web'])->group(function () {
    // Rota para buscar clientes (casos) para o chat - versão web
    Route::get('/clients', function() {
        try {
            $user = auth()->user();
            $totalCases = \App\Models\LegalCase::count();
            
            \Log::info('Chat clients request', [
                'user_id' => $user?->id,
                'user_company_id' => $user?->company_id,
                'is_super_admin' => $user?->isSuperAdmin(),
                'total_cases_in_db' => $totalCases
            ]);
            
            $query = \App\Models\LegalCase::select('id', 'client_name as name');
            
            // Filtrar por empresa se não for super admin
            if (!$user->isSuperAdmin()) {
                $query->byCompany($user->company_id);
            }
            
            $clients = $query->get();
            
            \Log::info('Chat clients result', [
                'clients_count' => $clients->count(),
                'first_few_clients' => $clients->take(3)->toArray()
            ]);
            
            return response()->json($clients);
        } catch (\Exception $e) {
            \Log::error('Error in chat clients endpoint', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Erro ao carregar clientes'], 500);
        }
    });
    
    // Rota para chat com IA - versão web
    Route::post('/ai-chat', [DeepSeekChatController::class, '__invoke'])->name('api.ai-chat-web');
    
    // Rota para carregar histórico de mensagens - versão web
    Route::get('/chat-messages', [DeepSeekChatController::class, 'getMessages'])->name('api.chat-messages-web');
});

Route::get('/rota-teste', function() {
    return response()->json(['ok' => true]);
});

// Rotas para employment-relationships (temporariamente sem autenticação para debug)
Route::post('/employment-relationships', [EmploymentRelationshipController::class, 'store']);
Route::patch('/employment-relationships/{id}', [EmploymentRelationshipController::class, 'update']);
Route::delete('/employment-relationships/{id}', [EmploymentRelationshipController::class, 'destroy']);
Route::get('/employment-relationships/{id}/tentativas', [CollectionAttemptController::class, 'index']);
Route::patch('/employment-relationships/{id}/tentativas/{tentativa}', [CollectionAttemptController::class, 'update']);

Route::middleware(['auth:sanctum', 'api.cors'])->group(function () {
    // Route::patch('/employment-relationships/{id}', [EmploymentRelationshipController::class, 'update']);
    // Route::get('/employment-relationships/{id}/tentativas', [CollectionAttemptController::class, 'index']);
    // Route::patch('/employment-relationships/{id}/tentativas/{tentativa}', [CollectionAttemptController::class, 'update']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    
    // Rota para buscar tarefas do caso
    Route::get('/cases/{case}/tasks', [\App\Http\Controllers\CaseController::class, 'getCaseTasks'])
        ->name('api.cases.tasks');
});

// Petition API routes
Route::post('/generate-petition', [PetitionController::class, 'generateWithAI'])->name('api.petitions.generate-ai');
Route::post('/generate-from-template', [PetitionController::class, 'generateFromTemplate'])->name('api.petitions.generate-template');

// External API routes for Chrome extension (without auth, using API key)
Route::middleware(['api.cors'])->group(function () {
    Route::get('/extension/get-id-empresa', [CompanyApiController::class, 'getIdEmpresa']);
    Route::post('/extension/sync', [ProcessoSyncController::class, 'sync']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Rotas de Usuários
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{userId}', [UserController::class, 'show']);

    // Rotas de Tarefas
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{taskId}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'store']);

    // Rotas de Processos Judiciais
    Route::get('/lawsuits', [LawsuitController::class, 'index']);
    Route::get('/lawsuits/{lawsuitId}', [LawsuitController::class, 'show']);
    Route::post('/lawsuits', [LawsuitController::class, 'store']);

    // Rotas de Movimentações de Processos
    Route::post('/lawsuits/movements', [LawsuitController::class, 'createMovement']);
});
