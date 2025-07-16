<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Lista de usuários para o AdvBox
     */
    public function index(Request $request)
    {
        // Filtros opcionais
        $query = User::query();

        // Filtrar por nome (opcional)
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filtrar por departamento ou função (opcional)
        if ($request->has('department')) {
            $query->where('department', $request->input('department'));
        }

        // Ordenar por nome
        $query->orderBy('name');

        // Paginação opcional
        $perPage = $request->input('per_page', 50);
        $users = $query->paginate($perPage);

        // Transformar para o formato esperado
        return response()->json(
            $users->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'department' => $user->department ?? null,
                ];
            })
        );
    }

    /**
     * Detalhes de um usuário específico
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);

        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'department' => $user->department ?? null,
        ]);
    }
} 