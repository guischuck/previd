<?php

// Arquivo: public/debug.php
// Acesse: https://seudominio.com/debug.php

// Headers para debug
header('Content-Type: text/html; charset=utf-8');

// Incluir Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $kernel->bootstrap();
} catch (Exception $e) {
    die("Erro ao inicializar Laravel: " . $e->getMessage());
}

function formatResult($title, $success, $message = '', $data = null) {
    $color = $success ? '#28a745' : '#dc3545';
    $icon = $success ? '✅' : '❌';
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid $color; background: " . ($success ? '#d4edda' : '#f8d7da') . ";'>";
    echo "<strong>$icon $title</strong><br>";
    if ($message) echo "<span style='color: #666;'>$message</span><br>";
    if ($data) {
        echo "<pre style='background: #f8f9fa; padding: 10px; margin: 5px 0; font-size: 12px; overflow: auto;'>";
        echo htmlspecialchars(print_r($data, true));
        echo "</pre>";
    }
    echo "</div>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Sistema Jurídico</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .container { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; }
        .header { background: #007bff; color: white; padding: 15px; margin: -20px -20px 20px; border-radius: 8px 8px 0 0; }
        .test-section { margin: 20px 0; }
        .form-test { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Debug Sistema Jurídico</h1>
            <p>Diagnóstico completo do sistema - <?= date('d/m/Y H:i:s') ?></p>
        </div>

        <div class="test-section">
            <h2>📊 Testes do Sistema</h2>

            <?php
            // 1. Teste de conexão com banco
            try {
                $pdo = DB::connection()->getPdo();
                formatResult("Conexão com Banco", true, "Conectado com sucesso");
            } catch (Exception $e) {
                formatResult("Conexão com Banco", false, $e->getMessage());
            }

            // 2. Verificar tabelas essenciais
            $tables = ['users', 'companies', 'cases', 'employment_relationships'];
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    formatResult("Tabela $table", true, "$count registros encontrados");
                } catch (Exception $e) {
                    formatResult("Tabela $table", false, $e->getMessage());
                }
            }

            // 3. Verificar usuários
            try {
                $users = DB::table('users')->select('id', 'name', 'email', 'company_id')->get();
                formatResult("Usuários", true, count($users) . " usuários encontrados", $users->toArray());
            } catch (Exception $e) {
                formatResult("Usuários", false, $e->getMessage());
            }

            // 4. Verificar empresas
            try {
                $companies = DB::table('companies')->select('id', 'name', 'slug', 'is_active')->get();
                formatResult("Empresas", true, count($companies) . " empresas encontradas", $companies->toArray());
            } catch (Exception $e) {
                formatResult("Empresas", false, $e->getMessage());
            }

            // 5. Teste de criação de caso (simulado)
            try {
                $user = DB::table('users')->first();
                if ($user && $user->company_id) {
                    $testData = [
                        'case_number' => 'TEST-' . time(),
                        'client_name' => 'Cliente Teste Debug',
                        'client_cpf' => '123.456.789-00',
                        'status' => 'pendente',
                        'created_by' => $user->id,
                        'company_id' => $user->company_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $caseId = DB::table('cases')->insertGetId($testData);
                    
                    // Remover o caso de teste
                    DB::table('cases')->where('id', $caseId)->delete();
                    
                    formatResult("Criação de Caso", true, "Caso criado e removido com sucesso (ID: $caseId)");
                } else {
                    formatResult("Criação de Caso", false, "Usuário sem company_id ou nenhum usuário encontrado");
                }
            } catch (Exception $e) {
                formatResult("Criação de Caso", false, $e->getMessage());
            }

            // 6. Verificar estrutura da tabela cases
            try {
                $columns = DB::select("DESCRIBE cases");
                $columnInfo = [];
                foreach ($columns as $col) {
                    $columnInfo[] = [
                        'Field' => $col->Field,
                        'Type' => $col->Type,
                        'Null' => $col->Null,
                        'Key' => $col->Key,
                        'Default' => $col->Default
                    ];
                }
                formatResult("Estrutura Tabela Cases", true, count($columns) . " colunas", $columnInfo);
            } catch (Exception $e) {
                formatResult("Estrutura Tabela Cases", false, $e->getMessage());
            }

            // 7. Verificar rotas
            try {
                $routes = [];
                foreach (Route::getRoutes() as $route) {
                    if (str_contains($route->uri(), 'cases')) {
                        $routes[] = [
                            'Method' => implode('|', $route->methods()),
                            'URI' => $route->uri(),
                            'Action' => $route->getActionName(),
                            'Middleware' => implode(', ', $route->middleware())
                        ];
                    }
                }
                formatResult("Rotas Cases", true, count($routes) . " rotas encontradas", $routes);
            } catch (Exception $e) {
                formatResult("Rotas Cases", false, $e->getMessage());
            }

            // 8. Teste de middleware
            try {
                $middlewares = app('router')->getMiddleware();
                $hasEnsureCompany = isset($middlewares['ensure.user.company']);
                formatResult("Middleware ensure.user.company", $hasEnsureCompany, $hasEnsureCompany ? "Middleware registrado" : "Middleware NÃO encontrado");
            } catch (Exception $e) {
                formatResult("Middleware", false, $e->getMessage());
            }

            // 9. Informações do servidor
            $serverInfo = [
                'PHP Version' => PHP_VERSION,
                'Laravel Version' => app()->version(),
                'Environment' => app()->environment(),
                'Debug Mode' => config('app.debug') ? 'ON' : 'OFF',
                'Database' => config('database.default'),
                'Cache Driver' => config('cache.default'),
                'Session Driver' => config('session.driver'),
            ];
            formatResult("Informações do Servidor", true, "", $serverInfo);
            ?>
        </div>

        <div class="test-section">
            <h2>🧪 Teste de Criação de Caso</h2>
            <div class="form-test">
                <h3>Formulário de Teste</h3>
                <form id="testForm" onsubmit="return testCaseCreation(event)">
                    <p><label>Nome: <input type="text" name="client_name" value="Cliente Teste Debug" required style="margin-left: 10px; padding: 5px; width: 200px;"></label></p>
                    <p><label>CPF: <input type="text" name="client_cpf" value="123.456.789-00" required style="margin-left: 10px; padding: 5px; width: 200px;"></label></p>
                    <p><button type="submit" class="btn">Testar Criação de Caso</button></p>
                </form>
                <div id="testResult"></div>
            </div>
        </div>

        <div class="test-section">
            <h2>📝 Logs Recentes</h2>
            <?php
            try {
                $logFile = storage_path('logs/laravel.log');
                if (file_exists($logFile)) {
                    $logContent = file_get_contents($logFile);
                    $logLines = array_slice(explode("\n", $logContent), -20); // Últimas 20 linhas
                    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto; max-height: 300px; font-size: 12px;'>";
                    echo htmlspecialchars(implode("\n", $logLines));
                    echo "</pre>";
                } else {
                    formatResult("Arquivo de Log", false, "Arquivo de log não encontrado: $logFile");
                }
            } catch (Exception $e) {
                formatResult("Logs", false, $e->getMessage());
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🔧 Ações Rápidas</h2>
            <button class="btn" onclick="location.reload()">🔄 Atualizar Página</button>
            <button class="btn" onclick="clearLogs()">🗑️ Limpar Logs</button>
            <button class="btn" onclick="window.open('/cases/create', '_blank')">📄 Abrir Criar Caso</button>
            <button class="btn" onclick="window.open('/cases', '_blank')">📋 Abrir Lista Casos</button>
        </div>
    </div>

    <script>
        function testCaseCreation(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());
            
            document.getElementById('testResult').innerHTML = '<p style="color: #007bff;">🔄 Testando criação de caso...</p>';
            
            fetch('/cases', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('testResult').innerHTML = 
                    '<p style="color: #28a745;">✅ Sucesso: ' + JSON.stringify(data) + '</p>';
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('testResult').innerHTML = 
                    '<p style="color: #dc3545;">❌ Erro: ' + error.message + '</p>';
            });
            
            return false;
        }
        
        function clearLogs() {
            if (confirm('Tem certeza que deseja limpar os logs?')) {
                fetch('<?= url("/debug.php?action=clear_logs") ?>')
                .then(() => {
                    alert('Logs limpos!');
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>

<?php
// Ação para limpar logs
if (isset($_GET['action']) && $_GET['action'] === 'clear_logs') {
    try {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>