<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Criar empresa padrão para o admin
        DB::table('companies')->insertOrIgnore([
            'id' => 1,
            'name' => 'Sistema Admin',
            'slug' => 'sistema-admin',
            'email' => 'admin@sistema.com',
            'plan' => 'enterprise',
            'max_users' => 999999,
            'max_cases' => 999999,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Atualizar ou criar usuário admin
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
                'company_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Não vamos remover o admin no rollback
    }
}; 