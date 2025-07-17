<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('company')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'stats' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'super_admin' => User::where('is_super_admin', true)->count(),
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'companies' => Company::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $request->company_id,
            'role' => $request->role,
            'is_active' => $request->is_active ?? true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function show(User $user)
    {
        $user->load('company');

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'stats' => [
                'cases_count' => $user->cases()->count(),
                'active_cases' => $user->cases()->whereIn('status', ['pendente', 'em_coleta'])->count(),
                'completed_cases' => $user->cases()->where('status', 'concluido')->count(),
            ]
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'companies' => Company::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $data = $request->except('password');
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        // Verificar se o usuário tem casos ativos
        if ($user->cases()->whereNotIn('status', ['concluido', 'cancelado'])->exists()) {
            return back()->with('error', 'Não é possível excluir um usuário com casos ativos.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }
}