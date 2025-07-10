<?php

// Arquivo: debug_case_creation.php (salve na raiz do projeto)
// Execute: php debug_case_creation.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->bootstrap();
} catch (Exception $e) {
    die("❌ Erro ao inicializar Laravel: " . $e->getMessage() . "\n");
}

function debug($title, $data = null) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🔍 $title\n";
    echo str_repeat("=", 50) . "\n";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            echo $data . "\n";
        }
    }
}

function test($description, $callback) {
    echo "\n🧪 TESTE: $description\n";
    try {
        $result = $callback();
        echo "✅ SUCESSO: $result\n";
        return true;
    } catch (Exception $e) {
        echo "❌ ERRO: " . $e->getMessage() . "\n";
        echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
        return false;
    }
}

echo "🚀 INICIANDO DEBUG COMPLETO DA CRIAÇÃO DE CASOS\n";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "\n";

// 1. TESTE DE CONEXÃO COM BANCO
test("Conexão com banco de dados", function() {
    $pdo = DB::connection()->getPdo();
    return "Conectado ao banco: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
});

// 2. VERIFICAR USUÁRIOS
test("Verificar usuários", function() {
    $users = \App\Models\User::select('id', 'name', 'email', 'company_id')->get();
    debug("Usuários encontrados", $users->toArray());
    
    $withoutCompany = $users->where('company_id', null)->count();
    if ($withoutCompany > 0) {
        throw new Exception("$withoutCompany usuários sem company_id");
    }
    
    return "Total: " . $users->count() . " usuários, todos com company_id";
});

// 3. VERIFICAR EMPRESAS
test("Verificar empresas", function() {
    $companies = \App\Models\Company::select('id', 'name', 'slug', 'is_active')->get();
    debug("Empresas encontradas", $companies->toArray());
    return "Total: " . $companies->count() . " empresas";
});

// 4. TESTE DO MÉTODO generateCaseNumber
test("Método generateCaseNumber", function() {
    $controller = new \App\Http\Controllers\CaseController();
    
    // Usar reflection para acessar método privado
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('generateCaseNumber');
    $method->setAccessible(true);
    
    $caseNumber = $method->invoke($controller);
    return "Número gerado: $caseNumber";
});

// 5. TESTE DOS MÉTODOS parseDate E parseSalary
test("Métodos parseDate e parseSalary", function() {
    $controller = new \App\Http\Controllers\CaseController();
    $reflection = new ReflectionClass($controller);
    
    // Teste parseDate
    $parseDate = $reflection->getMethod('parseDate');
    $parseDate->setAccessible(true);
    
    $date1 = $parseDate->invoke($controller, '01/01/2020');
    $date2 = $parseDate->invoke($controller, '');
    $date3 = $parseDate->invoke($controller, 'sem data fim');
    
    // Teste parseSalary
    $parseSalary = $reflection->getMethod('parseSalary');
    $parseSalary->setAccessible(true);
    
    $salary1 = $parseSalary->invoke($controller, '1.500,50');
    $salary2 = $parseSalary->invoke($controller, 'R$ 2.000,00');
    $salary3 = $parseSalary->invoke($controller, '');
    
    debug("Testes de parsing", [
        'parseDate("01/01/2020")' => $date1,
        'parseDate("")' => $date2,
        'parseDate("sem data fim")' => $date3,
        'parseSalary("1.500,50")' => $salary1,
        'parseSalary("R$ 2.000,00")' => $salary2,
        'parseSalary("")' => $salary3,
    ]);
    
    return "Métodos funcionando corretamente";
});

// 6. TESTE COMPLETO DE CRIAÇÃO DE CASO
test("Criação completa de caso", function() {
    $user = \App\Models\User::first();
    
    if (!$user) {
        throw new Exception("Nenhum usuário encontrado");
    }
    
    if (!$user->company_id) {
        throw new Exception("Usuário {$user->name} não tem company_id");
    }
    
    debug("Usuário para teste", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'company_id' => $user->company_id
    ]);
    
    // Dados de teste exatamente como o formulário envia
    $testData = [
        'client_name' => 'Cliente Teste Debug PHP',
        'client_cpf' => '123.456.789-00',
        'benefit_type' => 'aposentadoria_por_idade',
        'notes' => 'Teste criado via debug PHP',
        'vinculos_empregaticios' => [
            [
                'empregador' => 'Empresa Teste Ltda',
                'cnpj' => '12.345.678/0001-90',
                'data_inicio' => '01/01/2020',
                'data_fim' => '31/12/2022',
                'salario' => '2.500,00'
            ]
        ]
    ];
    
    debug("Dados de teste", $testData);
    
    // Simular o processo do controller
    $controller = new \App\Http\Controllers\CaseController();
    $reflection = new ReflectionClass($controller);
    
    // Gerar case number
    $generateCaseNumber = $reflection->getMethod('generateCaseNumber');
    $generateCaseNumber->setAccessible(true);
    $caseNumber = $generateCaseNumber->invoke($controller);
    
    // Preparar dados para criação
    $caseData = [
        'case_number' => $caseNumber,
        'client_name' => $testData['client_name'],
        'client_cpf' => $testData['client_cpf'],
        'benefit_type' => $testData['benefit_type'],
        'notes' => $testData['notes'],
        'status' => 'pendente',
        'created_by' => $user->id,
        'company_id' => $user->company_id,
    ];
    
    debug("Dados do caso para criação", $caseData);
    
    // Verificar se todos os campos obrigatórios estão presentes
    $requiredFields = ['case_number', 'client_name', 'client_cpf', 'status', 'created_by', 'company_id'];
    foreach ($requiredFields as $field) {
        if (!isset($caseData[$field]) || $caseData[$field] === null || $caseData[$field] === '') {
            throw new Exception("Campo obrigatório '$field' está vazio ou nulo");
        }
    }
    
    // Criar o caso
    $case = \App\Models\LegalCase::create($caseData);
    
    debug("Caso criado", [
        'id' => $case->id,
        'case_number' => $case->case_number,
        'client_name' => $case->client_name,
        'status' => $case->status
    ]);
    
    // Criar vínculos empregatícios
    $parseDate = $reflection->getMethod('parseDate');
    $parseDate->setAccessible(true);
    $parseSalary = $reflection->getMethod('parseSalary');
    $parseSalary->setAccessible(true);
    
    foreach ($testData['vinculos_empregaticios'] as $vinculo) {
        $employmentData = [
            'employer_name' => $vinculo['empregador'],
            'employer_cnpj' => $vinculo['cnpj'],
            'start_date' => $parseDate->invoke($controller, $vinculo['data_inicio']),
            'end_date' => $parseDate->invoke($controller, $vinculo['data_fim']),
            'salary' => $parseSalary->invoke($controller, $vinculo['salario']),
            'is_active' => empty($vinculo['data_fim']),
            'notes' => 'Teste via debug PHP',
        ];
        
        debug("Dados do vínculo", $employmentData);
        
        $employment = $case->employmentRelationships()->create($employmentData);
        
        debug("Vínculo criado", [
            'id' => $employment->id,
            'employer_name' => $employment->employer_name,
            'start_date' => $employment->start_date,
            'end_date' => $employment->end_date
        ]);
    }
    
    // Verificar se o caso foi criado corretamente
    $createdCase = \App\Models\LegalCase::with('employmentRelationships')->find($case->id);
    
    debug("Caso final criado", [
        'caso' => $createdCase->toArray(),
        'vinculos_count' => $createdCase->employmentRelationships->count()
    ]);
    
    // Limpar teste (opcional - descomente se quiser manter o caso)
    // $createdCase->employmentRelationships()->delete();
    // $createdCase->delete();
    
    return "Caso criado com sucesso! ID: {$case->id}, Vínculos: " . $createdCase->employmentRelationships->count();
});

// 7. TESTE DE VALIDAÇÃO
test("Validação de dados", function() {
    $validator = \Illuminate\Support\Facades\Validator::make([
        'client_name' => 'Teste',
        'client_cpf' => '123.456.789-00',
        'vinculos_empregaticios' => []
    ], [
        'client_name' => 'required|string|max:255',
        'client_cpf' => 'required|string|max:14',
        'vinculos_empregaticios' => 'nullable|array',
    ]);
    
    if ($validator->fails()) {
        throw new Exception("Validação falhou: " . implode(', ', $validator->errors()->all()));
    }
    
    return "Validação passou";
});

// 8. VERIFICAR ESTRUTURA DA TABELA CASES
test("Estrutura da tabela cases", function() {
    $columns = DB::select("DESCRIBE cases");
    
    debug("Colunas da tabela cases", array_map(function($col) {
        return [
            'Field' => $col->Field,
            'Type' => $col->Type,
            'Null' => $col->Null,
            'Key' => $col->Key,
            'Default' => $col->Default
        ];
    }, $columns));
    
    // Verificar campos obrigatórios
    $requiredFields = ['case_number', 'client_name', 'client_cpf', 'status', 'created_by', 'company_id'];
    $existingFields = array_column($columns, 'Field');
    
    foreach ($requiredFields as $field) {
        if (!in_array($field, $existingFields)) {
            throw new Exception("Campo obrigatório '$field' não existe na tabela");
        }
    }
    
    return "Estrutura da tabela está correta";
});

// 9. TESTE DE MIDDLEWARE (simulado)
test("Verificar middlewares", function() {
    $middlewares = app('router')->getMiddleware();
    
    debug("Middlewares registrados", array_keys($middlewares));
    
    // Verificar se middleware problemático existe
    if (isset($middlewares['ensure.user.company'])) {
        return "Middleware 'ensure.user.company' está registrado";
    } else {
        return "Middleware 'ensure.user.company' NÃO está registrado";
    }
});

echo "\n" . str_repeat("=", 70) . "\n";
echo "🎉 DEBUG COMPLETO FINALIZADO!\n";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
echo str_repeat("=", 70) . "\n";<?php

// Arquivo: debug_case_creation.php (salve na raiz do projeto)
// Execute: php debug_case_creation.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->bootstrap();
} catch (Exception $e) {
    die("❌ Erro ao inicializar Laravel: " . $e->getMessage() . "\n");
}

function debug($title, $data = null) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🔍 $title\n";
    echo str_repeat("=", 50) . "\n";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            echo $data . "\n";
        }
    }
}

function test($description, $callback) {
    echo "\n🧪 TESTE: $description\n";
    try {
        $result = $callback();
        echo "✅ SUCESSO: $result\n";
        return true;
    } catch (Exception $e) {
        echo "❌ ERRO: " . $e->getMessage() . "\n";
        echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
        return false;
    }
}

echo "🚀 INICIANDO DEBUG COMPLETO DA CRIAÇÃO DE CASOS\n";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "\n";

// 1. TESTE DE CONEXÃO COM BANCO
test("Conexão com banco de dados", function() {
    $pdo = DB::connection()->getPdo();
    return "Conectado ao banco: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
});

// 2. VERIFICAR USUÁRIOS
test("Verificar usuários", function() {
    $users = \App\Models\User::select('id', 'name', 'email', 'company_id')->get();
    debug("Usuários encontrados", $users->toArray());
    
    $withoutCompany = $users->where('company_id', null)->count();
    if ($withoutCompany > 0) {
        throw new Exception("$withoutCompany usuários sem company_id");
    }
    
    return "Total: " . $users->count() . " usuários, todos com company_id";
});

// 3. VERIFICAR EMPRESAS
test("Verificar empresas", function() {
    $companies = \App\Models\Company::select('id', 'name', 'slug', 'is_active')->get();
    debug("Empresas encontradas", $companies->toArray());
    return "Total: " . $companies->count() . " empresas";
});

// 4. TESTE DO MÉTODO generateCaseNumber
test("Método generateCaseNumber", function() {
    $controller = new \App\Http\Controllers\CaseController();
    
    // Usar reflection para acessar método privado
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('generateCaseNumber');
    $method->setAccessible(true);
    
    $caseNumber = $method->invoke($controller);
    return "Número gerado: $caseNumber";
});

// 5. TESTE DOS MÉTODOS parseDate E parseSalary
test("Métodos parseDate e parseSalary", function() {
    $controller = new \App\Http\Controllers\CaseController();
    $reflection = new ReflectionClass($controller);
    
    // Teste parseDate
    $parseDate = $reflection->getMethod('parseDate');
    $parseDate->setAccessible(true);
    
    $date1 = $parseDate->invoke($controller, '01/01/2020');
    $date2 = $parseDate->invoke($controller, '');
    $date3 = $parseDate->invoke($controller, 'sem data fim');
    
    // Teste parseSalary
    $parseSalary = $reflection->getMethod('parseSalary');
    $parseSalary->setAccessible(true);
    
    $salary1 = $parseSalary->invoke($controller, '1.500,50');
    $salary2 = $parseSalary->invoke($controller, 'R$ 2.000,00');
    $salary3 = $parseSalary->invoke($controller, '');
    
    debug("Testes de parsing", [
        'parseDate("01/01/2020")' => $date1,
        'parseDate("")' => $date2,
        'parseDate("sem data fim")' => $date3,
        'parseSalary("1.500,50")' => $salary1,
        'parseSalary("R$ 2.000,00")' => $salary2,
        'parseSalary("")' => $salary3,
    ]);
    
    return "Métodos funcionando corretamente";
});

// 6. TESTE COMPLETO DE CRIAÇÃO DE CASO
test("Criação completa de caso", function() {
    $user = \App\Models\User::first();
    
    if (!$user) {
        throw new Exception("Nenhum usuário encontrado");
    }
    
    if (!$user->company_id) {
        throw new Exception("Usuário {$user->name} não tem company_id");
    }
    
    debug("Usuário para teste", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'company_id' => $user->company_id
    ]);
    
    // Dados de teste exatamente como o formulário envia
    $testData = [
        'client_name' => 'Cliente Teste Debug PHP',
        'client_cpf' => '123.456.789-00',
        'benefit_type' => 'aposentadoria_por_idade',
        'notes' => 'Teste criado via debug PHP',
        'vinculos_empregaticios' => [
            [
                'empregador' => 'Empresa Teste Ltda',
                'cnpj' => '12.345.678/0001-90',
                'data_inicio' => '01/01/2020',
                'data_fim' => '31/12/2022',
                'salario' => '2.500,00'
            ]
        ]
    ];
    
    debug("Dados de teste", $testData);
    
    // Simular o processo do controller
    $controller = new \App\Http\Controllers\CaseController();
    $reflection = new ReflectionClass($controller);
    
    // Gerar case number
    $generateCaseNumber = $reflection->getMethod('generateCaseNumber');
    $generateCaseNumber->setAccessible(true);
    $caseNumber = $generateCaseNumber->invoke($controller);
    
    // Preparar dados para criação
    $caseData = [
        'case_number' => $caseNumber,
        'client_name' => $testData['client_name'],
        'client_cpf' => $testData['client_cpf'],
        'benefit_type' => $testData['benefit_type'],
        'notes' => $testData['notes'],
        'status' => 'pendente',
        'created_by' => $user->id,
        'company_id' => $user->company_id,
    ];
    
    debug("Dados do caso para criação", $caseData);
    
    // Verificar se todos os campos obrigatórios estão presentes
    $requiredFields = ['case_number', 'client_name', 'client_cpf', 'status', 'created_by', 'company_id'];
    foreach ($requiredFields as $field) {
        if (!isset($caseData[$field]) || $caseData[$field] === null || $caseData[$field] === '') {
            throw new Exception("Campo obrigatório '$field' está vazio ou nulo");
        }
    }
    
    // Criar o caso
    $case = \App\Models\LegalCase::create($caseData);
    
    debug("Caso criado", [
        'id' => $case->id,
        'case_number' => $case->case_number,
        'client_name' => $case->client_name,
        'status' => $case->status
    ]);
    
    // Criar vínculos empregatícios
    $parseDate = $reflection->getMethod('parseDate');
    $parseDate->setAccessible(true);
    $parseSalary = $reflection->getMethod('parseSalary');
    $parseSalary->setAccessible(true);
    
    foreach ($testData['vinculos_empregaticios'] as $vinculo) {
        $employmentData = [
            'employer_name' => $vinculo['empregador'],
            'employer_cnpj' => $vinculo['cnpj'],
            'start_date' => $parseDate->invoke($controller, $vinculo['data_inicio']),
            'end_date' => $parseDate->invoke($controller, $vinculo['data_fim']),
            'salary' => $parseSalary->invoke($controller, $vinculo['salario']),
            'is_active' => empty($vinculo['data_fim']),
            'notes' => 'Teste via debug PHP',
        ];
        
        debug("Dados do vínculo", $employmentData);
        
        $employment = $case->employmentRelationships()->create($employmentData);
        
        debug("Vínculo criado", [
            'id' => $employment->id,
            'employer_name' => $employment->employer_name,
            'start_date' => $employment->start_date,
            'end_date' => $employment->end_date
        ]);
    }
    
    // Verificar se o caso foi criado corretamente
    $createdCase = \App\Models\LegalCase::with('employmentRelationships')->find($case->id);
    
    debug("Caso final criado", [
        'caso' => $createdCase->toArray(),
        'vinculos_count' => $createdCase->employmentRelationships->count()
    ]);
    
    // Limpar teste (opcional - descomente se quiser manter o caso)
    // $createdCase->employmentRelationships()->delete();
    // $createdCase->delete();
    
    return "Caso criado com sucesso! ID: {$case->id}, Vínculos: " . $createdCase->employmentRelationships->count();
});

// 7. TESTE DE VALIDAÇÃO
test("Validação de dados", function() {
    $validator = \Illuminate\Support\Facades\Validator::make([
        'client_name' => 'Teste',
        'client_cpf' => '123.456.789-00',
        'vinculos_empregaticios' => []
    ], [
        'client_name' => 'required|string|max:255',
        'client_cpf' => 'required|string|max:14',
        'vinculos_empregaticios' => 'nullable|array',
    ]);
    
    if ($validator->fails()) {
        throw new Exception("Validação falhou: " . implode(', ', $validator->errors()->all()));
    }
    
    return "Validação passou";
});

// 8. VERIFICAR ESTRUTURA DA TABELA CASES
test("Estrutura da tabela cases", function() {
    $columns = DB::select("DESCRIBE cases");
    
    debug("Colunas da tabela cases", array_map(function($col) {
        return [
            'Field' => $col->Field,
            'Type' => $col->Type,
            'Null' => $col->Null,
            'Key' => $col->Key,
            'Default' => $col->Default
        ];
    }, $columns));
    
    // Verificar campos obrigatórios
    $requiredFields = ['case_number', 'client_name', 'client_cpf', 'status', 'created_by', 'company_id'];
    $existingFields = array_column($columns, 'Field');
    
    foreach ($requiredFields as $field) {
        if (!in_array($field, $existingFields)) {
            throw new Exception("Campo obrigatório '$field' não existe na tabela");
        }
    }
    
    return "Estrutura da tabela está correta";
});

// 9. TESTE DE MIDDLEWARE (simulado)
test("Verificar middlewares", function() {
    $middlewares = app('router')->getMiddleware();
    
    debug("Middlewares registrados", array_keys($middlewares));
    
    // Verificar se middleware problemático existe
    if (isset($middlewares['ensure.user.company'])) {
        return "Middleware 'ensure.user.company' está registrado";
    } else {
        return "Middleware 'ensure.user.company' NÃO está registrado";
    }
});

echo "\n" . str_repeat("=", 70) . "\n";
echo "🎉 DEBUG COMPLETO FINALIZADO!\n";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
echo str_repeat("=", 70) . "\n";
