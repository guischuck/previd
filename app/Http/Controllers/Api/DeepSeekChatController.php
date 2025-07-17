<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeepSeekService;
use App\Models\LegalCase;
use App\Models\EmploymentRelationship;
use App\Models\Document;
use App\Models\Task;
use App\Models\ChatMessage; // Importar o modelo de mensagens
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeepSeekChatController extends Controller
{
    public function getMessages(Request $request)
    {
        Log::info('getMessages called', [
            'case_id' => $request->get('case_id'),
            'all_params' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);
        
        // Temporariamente removendo autenticação para debug
        $caseId = $request->get('case_id');
        
        if (!$caseId) {
            Log::error('case_id not provided');
            return response()->json(['error' => 'case_id is required'], 400);
        }
        
        Log::info('Searching for messages', ['case_id' => $caseId]);
        
        $messages = ChatMessage::where('case_id', $caseId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        Log::info('Messages found', [
            'count' => $messages->count(),
            'messages' => $messages->toArray()
        ]);

        return response()->json($messages);
    }

    public function __invoke(Request $request, DeepSeekService $deepSeek)
    {
        Log::info('DeepSeek Chat Request received', [
            'client_id' => $request->client_id,
            'message' => substr($request->message ?? '', 0, 100),
            'user_id' => auth()->id(),
        ]);

        $request->validate([
            'client_id' => 'nullable|integer',
            'message' => 'required|string',
        ]);

        $userContext = '';
        $clientName = 'Usuário';
        
        if ($request->client_id) {
            $caseQuery = LegalCase::where('id', $request->client_id);
            
            if (!auth()->user()->isSuperAdmin()) {
                $caseQuery->byCompany(auth()->user()->company_id);
            }
            
            $case = $caseQuery->first();
            
            if ($case) {
                $clientName = $case->client_name;
                
                $employmentRelationships = EmploymentRelationship::where('case_id', $case->id)->get();
                $documents = Document::where('case_id', $case->id)->get();
                $tasks = Task::where('case_id', $case->id)->orderBy('created_at', 'desc')->take(10)->get();
                
                $userContext = "=== INFORMAÇÕES DO CLIENTE ===\n";
                $userContext .= "Nome: {$case->client_name}\n";
                $userContext .= "CPF: {$case->client_cpf}\n";
                $userContext .= "Número do Caso: {$case->case_number}\n";
                $userContext .= "Tipo de Benefício: " . ($case->benefit_type ?? 'Não especificado') . "\n";
                $userContext .= "Status: {$case->status}\n";
                $userContext .= "Descrição: " . ($case->description ?? 'Não informada') . "\n";
                $userContext .= "Notas: " . ($case->notes ?? 'Nenhuma') . "\n";
                $userContext .= "Valor Estimado: " . ($case->estimated_value ? 'R$ ' . number_format($case->estimated_value, 2, ',', '.') : 'Não informado') . "\n";
                $userContext .= "Taxa de Sucesso: " . ($case->success_fee ? $case->success_fee . '%' : 'Não informada') . "\n\n";
                
                if ($employmentRelationships->count() > 0) {
                    $userContext .= "=== VÍNCULOS EMPREGATÍCIOS ===\n";
                    foreach ($employmentRelationships as $employment) {
                        $userContext .= "- {$employment->employer_name}\n";
                        $userContext .= "  Período: " . $employment->start_date->format('d/m/Y');
                        if ($employment->end_date) {
                            $userContext .= " até " . $employment->end_date->format('d/m/Y');
                        } else {
                            $userContext .= " até atual";
                        }
                        $userContext .= "\n";
                        $userContext .= "  Função: " . ($employment->job_title ?? 'Não informada') . "\n";
                        $userContext .= "  Salário: " . ($employment->salary ? 'R$ ' . number_format($employment->salary, 2, ',', '.') : 'Não informado') . "\n";
                        $userContext .= "  Status: {$employment->status}\n";
                        if ($employment->description) {
                            $userContext .= "  Observações: {$employment->description}\n";
                        }
                        $userContext .= "\n";
                    }
                } else {
                    $userContext .= "=== VÍNCULOS EMPREGATÍCIOS ===\n";
                    $userContext .= "Nenhum vínculo empregatício registrado ainda.\n\n";
                }
                
                if ($documents->count() > 0) {
                    $userContext .= "=== DOCUMENTOS ===\n";
                    foreach ($documents as $document) {
                        $userContext .= "- {$document->name}\n";
                        $userContext .= "  Tipo: {$document->type}\n";
                        $userContext .= "  Enviado em: " . $document->created_at->format('d/m/Y H:i') . "\n";
                        if ($document->description) {
                            $userContext .= "  Descrição: {$document->description}\n";
                        }
                    }
                    $userContext .= "\n";
                } else {
                    $userContext .= "=== DOCUMENTOS ===\n";
                    $userContext .= "Nenhum documento enviado ainda.\n\n";
                }
                
                if ($tasks->count() > 0) {
                    $userContext .= "=== ANDAMENTOS RECENTES ===\n";
                    foreach ($tasks as $task) {
                        $userContext .= "- {$task->title}\n";
                        $userContext .= "  Status: {$task->status}\n";
                        $userContext .= "  Data: " . $task->created_at->format('d/m/Y H:i') . "\n";
                        if ($task->description) {
                            $userContext .= "  Descrição: {$task->description}\n";
                        }
                        $userContext .= "\n";
                    }
                } else {
                    $userContext .= "=== ANDAMENTOS RECENTES ===\n";
                    $userContext .= "Nenhum andamento registrado ainda.\n\n";
                }
                
                $userContext .= "=== PERGUNTA DO USUÁRIO ===\n";
                
                Log::info('Case context loaded', [
                    'case_id' => $case->id, 
                    'client_name' => $case->client_name,
                    'employment_relationships' => $employmentRelationships->count(),
                    'documents' => $documents->count(),
                    'tasks' => $tasks->count(),
                ]);
            }
        }

        // Save user message
        if (auth()->check()) {
            try {
                $userMessage = ChatMessage::create([
                    'user_id' => auth()->id(),
                    'case_id' => $request->client_id,
                    'sender' => 'user',
                    'content' => $request->message,
                ]);
                
                Log::info('User message saved', [
                    'id' => $userMessage->id,
                    'user_id' => auth()->id(),
                    'case_id' => $request->client_id,
                    'content' => substr($request->message, 0, 100),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save user message', [
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                    'case_id' => $request->client_id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $prompt = $userContext . $request->message;

        try {
            Log::info('Calling DeepSeek API', ['prompt_length' => strlen($prompt)]);
            
            $response = $deepSeek->chat($prompt);
            
            Log::info('DeepSeek API response', [
                'success' => $response['success'] ?? false,
                'content_length' => strlen($response['content'] ?? ''),
            ]);
            
            if ($response['success']) {
                $aiResponse = $response['content'] ?? 'Resposta não disponível';
                
                // Save AI response
                if (auth()->check()) {
                    try {
                        $assistantMessage = ChatMessage::create([
                            'user_id' => auth()->id(),
                            'case_id' => $request->client_id,
                            'sender' => 'assistant',
                            'content' => $aiResponse,
                        ]);
                        
                        Log::info('Assistant message saved', [
                            'id' => $assistantMessage->id,
                            'user_id' => auth()->id(),
                            'case_id' => $request->client_id,
                            'content' => substr($aiResponse, 0, 100),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to save assistant message', [
                            'error' => $e->getMessage(),
                            'user_id' => auth()->id(),
                            'case_id' => $request->client_id,
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'response' => $aiResponse,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'response' => $response['error'] ?? 'Erro ao processar mensagem.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro no chat DeepSeek: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            if (strpos($e->getMessage(), 'API Key') !== false) {
                return response()->json([
                    'success' => false,
                    'response' => '⚙️ **Sistema de Chat Não Configurado**

O chat com IA precisa ser configurado pelo administrador. 

**Para administradores:**
1. Configure a variável `DEEPSEEK_API_KEY` no arquivo `.env`
2. Reinicie o servidor

**A chave do DeepSeek está mais econômica que OpenAI.**',
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'response' => 'Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente em alguns instantes.',
            ], 500);
        }
    }
}
