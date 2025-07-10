<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário super admin
        User::updateOrCreate(
            ['email' => 'admin@previdia.com.br'],
            [
                'name' => 'Administrador',
                'email' => 'admin@previdia.com.br',
                'password' => Hash::make('admin123'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
