<?php

// Arquivo: fix_users_company.php (salve na raiz do projeto)
// Execute: php fix_users_company.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGINDO USUÁRIOS SEM COMPANY_ID ===\n";

try {
    // 1. Verificar usuários sem company_id
    $usersWithoutCompany = DB::table('users')->whereNull('company_id')->get();
    echo "Usuários sem company_id: " . count($usersWithoutCompany) . "\n";
    
    if (count($usersWithoutCompany) > 0) {
        // 2. Criar empresa padrão
        $company = DB::table('companies')->where('slug', 'empresa-padrao')->first();
        
        if (!$company) {
            echo "Criando empresa padrão...\n";
            $companyId = DB::table('companies')->insertGetId([
                'name' => 'Empresa Padrão',
                'slug' => 'empresa-padrao',
                'plan' => 'basic',
                'max_users' => 50,
                'max_cases' => 1000,
                'is_active' => 1,
                'api_key' => \Illuminate\Support\Str::random(32),
                'razao_social' => 'Empresa Padrão Ltda',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ Empresa criada com ID: $companyId\n";
        } else {
            $companyId = $company->id;
            echo "✅ Empresa padrão já existe (ID: $companyId)\n";
        }
        
        // 3. Associar usuários à empresa
        foreach ($usersWithoutCompany as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'company_id' => $companyId,
                    'updated_at' => now()
                ]);
            echo "✅ Usuário {$user->name} (ID: {$user->id}) associado à empresa\n";
        }
        
        echo "\n✅ Todos os usuários foram associados à empresa padrão!\n";
    } else {
        echo "✅ Todos os usuários já têm company_id definido\n";
    }
    
    // 4. Verificar resultado
    $allUsers = DB::table('users')->select('id', 'name', 'email', 'company_id')->get();
    echo "\n📊 RESUMO FINAL:\n";
    foreach ($allUsers as $user) {
        echo "- {$user->name} (ID: {$user->id}) -> Company: {$user->company_id}\n";
    }
    
    // 5. Testar criação de caso
    echo "\n🧪 TESTANDO CRIAÇÃO DE CASO...\n";
    $firstUser = $allUsers->first();
    
    if ($firstUser && $firstUser->company_id) {
        $testCaseData = [
            'case_number' => 'TEST-' . time(),
            'client_name' => 'Cliente Teste Fix',
            'client_cpf' => '123.456.789-00',
            'status' => 'pendente',
            'created_by' => $firstUser->id,
            'company_id' => $firstUser->company_id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $caseId = DB::table('cases')->insertGetId($testCaseData);
        echo "✅ Caso de teste criado com ID: $caseId\n";
        
        // Remover caso de teste
        DB::table('cases')->where('id', $caseId)->delete();
        echo "✅ Caso de teste removido\n";
        
        echo "\n🎉 SISTEMA CORRIGIDO COM SUCESSO!\n";
    } else {
        echo "❌ Ainda há problemas com os usuários\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}