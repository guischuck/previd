<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DocumentController;
// use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.super.admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Empresas
    Route::resource('companies', CompanyController::class)->names([
        'index' => 'admin.companies.index',
        'create' => 'admin.companies.create',
        'store' => 'admin.companies.store',
        'show' => 'admin.companies.show',
        'edit' => 'admin.companies.edit',
        'update' => 'admin.companies.update',
        'destroy' => 'admin.companies.destroy',
    ]);

    // Usuários
    Route::resource('users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    // Documentos
    Route::resource('documents', DocumentController::class)->names([
        'index' => 'admin.documents.index',
        'create' => 'admin.documents.create',
        'store' => 'admin.documents.store',
        'show' => 'admin.documents.show',
        'edit' => 'admin.documents.edit',
        'update' => 'admin.documents.update',
        'destroy' => 'admin.documents.destroy',
    ]);

    // Templates (comentado até que o TemplateController seja implementado)
    // Route::resource('templates', TemplateController::class)->names([
    //     'index' => 'admin.templates.index',
    //     'create' => 'admin.templates.create',
    //     'store' => 'admin.templates.store',
    //     'show' => 'admin.templates.show',
    //     'edit' => 'admin.templates.edit',
    //     'update' => 'admin.templates.update',
    //     'destroy' => 'admin.templates.destroy',
    // ]);

    // Configurações
    Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('admin.settings.update');
}); 