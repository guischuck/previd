<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-super-admin {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Verificar se o usuário já existe
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->error("Usuário com email {$email} já existe!");
            return 1;
        }

        try {
            $user = User::create([
                'name' => 'Suporte Previdia',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'company_id' => null, // Super admin não tem empresa
            ]);

            $this->info("Usuário super admin criado com sucesso!");
            $this->info("Email: {$user->email}");
            $this->info("Nome: {$user->name}");
            $this->info("Role: {$user->role}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Erro ao criar usuário: " . $e->getMessage());
            return 1;
        }
    }
} 