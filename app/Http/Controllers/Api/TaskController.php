<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Lista de tarefas para o AdvBox
     */
    public function index(Request $request)
    {
        // Filtros opcionais
        $query = Task::query();

        // Filtrar por nome (opcional)
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filtrar por categoria (opcional)
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Ordenar por nome
        $query->orderBy('name');

        // Paginação opcional
        $perPage = $request->input('per_page', 50);
        $tasks = $query->paginate($perPage);

        // Transformar para o formato esperado
        return response()->json(
            $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'category' => $task->category ?? null,
                    'description' => $task->description ?? null,
                ];
            })
        );
    }

    /**
     * Detalhes de uma tarefa específica
     */
    public function show($taskId)
    {
        $task = Task::findOrFail($taskId);

        return response()->json([
            'id' => $task->id,
            'name' => $task->name,
            'category' => $task->category ?? null,
            'description' => $task->description ?? null,
        ]);
    }

    /**
     * Criar uma nova tarefa
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $task = Task::create($validatedData);

        return response()->json([
            'id' => $task->id,
            'name' => $task->name,
            'category' => $task->category ?? null,
            'description' => $task->description ?? null,
        ], 201);
    }
} 