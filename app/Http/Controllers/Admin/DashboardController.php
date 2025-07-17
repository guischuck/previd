<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Document;
use App\Models\PetitionTemplate;
use App\Models\User;
use App\Models\WorkflowTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'companies' => [
                'total' => Company::count(),
                'active' => Company::where('is_active', true)->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
            ],
            'documents' => [
                'total' => Document::count(),
            ],
            'petitionTemplates' => [
                'total' => PetitionTemplate::count(),
                'active' => PetitionTemplate::where('is_active', true)->count(),
            ],
            'workflowTemplates' => [
                'total' => WorkflowTemplate::count(),
                'active' => WorkflowTemplate::where('is_active', true)->count(),
            ],
        ];

        $financial = [
            'monthly_revenue' => 0, // Implementar lógica de receita
            'recent_payments' => 0, // Implementar lógica de pagamentos
            'active_subscriptions' => 0, // Implementar lógica de assinaturas
        ];

        $recentCompanies = Company::withCount('users')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'financial' => $financial,
            'recentCompanies' => $recentCompanies,
        ]);
    }
} 