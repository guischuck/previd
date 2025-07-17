<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lawsuit;
use Illuminate\Http\Request;
use App\Models\LawsuitMovement; // Added this import

class LawsuitController extends Controller
{
    /**
     * Buscar processos judiciais
     */
    public function index(Request $request)
    {
        // Filtros de busca
        $query = Lawsuit::query();

        // Filtrar por número de protocolo
        if ($request->has('protocol_number')) {
            $query->where('protocol_number', $request->input('protocol_number'));
        }

        // Filtrar por número do processo
        if ($request->has('process_number')) {
            $query->where('process_number', $request->input('process_number'));
        }

        // Filtrar por nome do cliente
        if ($request->has('customer_name')) {
            $query->whereHas('customers', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('customer_name') . '%');
            });
        }

        // Filtrar por status do processo
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Ordenar por data de criação (mais recentes primeiro)
        $query->orderBy('created_at', 'desc');

        // Paginação
        $perPage = $request->input('per_page', 20);
        $lawsuits = $query->paginate($perPage);

        // Transformar para o formato esperado
        return response()->json(
            $lawsuits->map(function ($lawsuit) {
                return [
                    'id' => $lawsuit->id,
                    'process_number' => $lawsuit->process_number,
                    'protocol_number' => $lawsuit->protocol_number,
                    'status' => $lawsuit->status,
                    'customers' => $lawsuit->customers->map(function ($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'identification' => $customer->identification,
                        ];
                    }),
                    'created_at' => $lawsuit->created_at->toDateTimeString(),
                ];
            })
        );
    }

    /**
     * Detalhes de um processo judicial específico
     */
    public function show($lawsuitId)
    {
        $lawsuit = Lawsuit::with(['customers', 'movements', 'tasks'])->findOrFail($lawsuitId);

        return response()->json([
            'id' => $lawsuit->id,
            'process_number' => $lawsuit->process_number,
            'protocol_number' => $lawsuit->protocol_number,
            'status' => $lawsuit->status,
            'customers' => $lawsuit->customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'identification' => $customer->identification,
                ];
            }),
            'movements' => $lawsuit->movements->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'date' => $movement->date,
                    'description' => $movement->description,
                    'type' => $movement->type,
                ];
            }),
            'tasks' => $lawsuit->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'status' => $task->status,
                    'deadline' => $task->deadline,
                ];
            }),
            'created_at' => $lawsuit->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Criar um novo processo judicial
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'process_number' => 'nullable|string|unique:lawsuits,process_number',
            'protocol_number' => 'nullable|string|unique:lawsuits,protocol_number',
            'status' => 'required|string|in:em_andamento,concluido,suspenso',
            'customers' => 'required|array',
            'customers.*' => 'exists:customers,id',
        ]);

        $lawsuit = Lawsuit::create([
            'process_number' => $validatedData['process_number'] ?? null,
            'protocol_number' => $validatedData['protocol_number'] ?? null,
            'status' => $validatedData['status'],
        ]);

        // Associar clientes ao processo
        $lawsuit->customers()->sync($validatedData['customers']);

        return response()->json([
            'id' => $lawsuit->id,
            'process_number' => $lawsuit->process_number,
            'protocol_number' => $lawsuit->protocol_number,
            'status' => $lawsuit->status,
        ], 201);
    }

    // Adicionar método createMovement
    public function createMovement(Request $request)
    {
        $validatedData = $request->validate([
            'lawsuit_id' => 'required|exists:lawsuits,id',
            'date' => 'required|date',
            'description' => 'required|string',
            'type' => 'nullable|in:judicial,administrativa,recursal'
        ]);

        try {
            $movement = LawsuitMovement::create([
                'lawsuit_id' => $validatedData['lawsuit_id'],
                'date' => $validatedData['date'],
                'description' => $validatedData['description'],
                'type' => $validatedData['type'] ?? 'judicial'
            ]);

            return response()->json([
                'success' => true,
                'movement_id' => $movement->id,
                'message' => 'Movimento criado com sucesso'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar movimento: ' . $e->getMessage()
            ], 500);
        }
    }
} 