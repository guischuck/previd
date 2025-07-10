<?php

// Arquivo: public/fix_case_number.php
// Acesse: https://previdia.com.br/fix_case_number.php

// Headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Incluir Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $kernel->bootstrap();
} catch (Exception $e) {
    die("Erro ao inicializar Laravel: " . $e->getMessage());
}

function logMessage($message, $type = 'info') {
    $color = $type === 'success' ? '#28a745' : ($type === 'error' ? '#dc3545' : '#007bff');
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid $color; background: " . 
         ($type === 'success' ? '#d4edda' : ($type === 'error' ? '#f8d7da' : '#d1ecf1')) . ";'>";
    echo "<strong>" . ucfirst($type) . ":</strong> " . htmlspecialchars($message);
    echo "</div>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Case Number Generator</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .container { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; }
        .header { background: #dc3545; color: white; padding: 15px; margin: -20px -20px 20px; border-radius: 8px 8px 0 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Fix Case Number Generator</h1>
            <p>Corrigir problema de números duplicados - <?= date('d/m/Y H:i:s') ?></p>
        </div>

        <?php
        if (isset($_GET['action'])):
            switch ($_GET['action']):
                case 'analyze':
                    logMessage("Analisando casos existentes...");
                    
                    try {
                        // Verificar casos duplicados
                        $duplicates = \App\Models\LegalCase::select('case_number')
                            ->groupBy('case_number')
                            ->having(\DB::raw('count(*)'), '>', 1)
                            ->get();
                        
                        if ($duplicates->count() > 0) {
                            logMessage("Encontrados " . $duplicates->count() . " números duplicados:", 'error');
                            foreach ($duplicates as $dup) {
                                $count = \App\Models\LegalCase::where('case_number', $dup->case_number)->count();
                                echo "<li>{$dup->case_number} (aparece {$count} vezes)</li>";
                            }
                        } else {
                            logMessage("Nenhum caso duplicado encontrado!", 'success');
                        }
                        
                        // Verificar último número
                        $year = date('Y');
                        $lastCase = \App\Models\LegalCase::where('case_number', 'like', "CASE-{$year}-%")
                            ->orderBy('case_number', 'desc')
                            ->first();
                        
                        if ($lastCase) {
                            logMessage("Último caso do ano {$year}: {$lastCase->case_number}");
                        } else {
                            logMessage("Nenhum caso encontrado para o ano {$year}");
                        }
                        
                        // Listar todos os casos de 2025
                        $cases2025 = \App\Models\LegalCase::where('case_number', 'like', 'CASE-2025-%')
                            ->orderBy('case_number')
                            ->get(['id', 'case_number', 'client_name', 'created_at']);
                        
                        if ($cases2025->count() > 0) {
                            echo "<h3>Casos de 2025:</h3>";
                            echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
                            echo "<tr style='background:#f8f9fa;'><th>ID</th><th>Número</th><th>Cliente</th><th>Criado em</th></tr>";
                            foreach ($cases2025 as $case) {
                                echo "<tr>";
                                echo "<td>{$case->id}</td>";
                                echo "<td>{$case->case_number}</td>";
                                echo "<td>{$case->client_name}</td>";
                                echo "<td>{$case->created_at}</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                        
                    } catch (Exception $e) {
                        logMessage("Erro na análise: " . $e->getMessage(), 'error');
                    }
                    break;
                    
                case 'fix_duplicates':
                    logMessage("Removendo casos duplicados...");
                    
                    try {
                        $duplicates = \App\Models\LegalCase::select('case_number')
                            ->groupBy('case_number')
                            ->having(\DB::raw('count(*)'), '>', 1)
                            ->pluck('case_number');
                        
                        $deletedCount = 0;
                        foreach ($duplicates as $caseNumber) {
                            $cases = \App\Models\LegalCase::where('case_number', $caseNumber)
                                ->orderBy('id', 'asc')
                                ->get();
                            
                            // Manter apenas o último, deletar os outros
                            for ($i = 0; $i < $cases->count() - 1; $i++) {
                                logMessage("Deletando caso duplicado: ID {$cases[$i]->id} - {$cases[$i]->case_number}");
                                $cases[$i]->delete();
                                $deletedCount++;
                            }
                        }
                        
                        logMessage("Total de casos duplicados removidos: {$deletedCount}", 'success');
                        
                    } catch (Exception $e) {
                        logMessage("Erro ao remover duplicados: " . $e->getMessage(), 'error');
                    }
                    break;
                    
                case 'fix_generator':
                    logMessage("Atualizando método generateCaseNumber...");
                    
                    try {
                        $controllerPath = __DIR__ . '/../app/Http/Controllers/CaseController.php';
                        $content = file_get_contents($controllerPath);
                        
                        // Novo método
                        $newMethod = '    private function generateCaseNumber(): string
    {
        $year = date(\'Y\');
        $prefix = "CASE-{$year}-";
        
        // Buscar todos os números existentes para este ano
        $existingNumbers = \App\Models\LegalCase::where(\'case_number\', \'like\', $prefix . \'%\')
            ->pluck(\'case_number\')
            ->map(function($caseNumber) use ($prefix) {
                return (int) str_replace($prefix, \'\', $caseNumber);
            })
            ->sort()
            ->values();
        
        // Encontrar o próximo número disponível
        $newNumber = 1;
        foreach ($existingNumbers as $number) {
            if ($number == $newNumber) {
                $newNumber++;
            } else {
                break;
            }
        }
        
        return $prefix . str_pad($newNumber, 4, \'0\', STR_PAD_LEFT);
    }';
                        
                        // Encontrar e substituir o método existente
                        $pattern = '/private function generateCaseNumber\(\): string\s*\{[^}]*\}/s';
                        if (preg_match($pattern, $content)) {
                            $newContent = preg_replace($pattern, $newMethod, $content);
                            
                            if (file_put_contents($controllerPath, $newContent)) {
                                logMessage("Método generateCaseNumber atualizado com sucesso!", 'success');
                                logMessage("Executando php artisan cache:clear...");
                                
                                // Limpar cache
                                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                                logMessage("Cache limpo com sucesso!", 'success');
                                
                            } else {
                                logMessage("Erro ao salvar arquivo", 'error');
                            }
                        } else {
                            logMessage("Método generateCaseNumber não encontrado no arquivo", 'error');
                        }
                        
                    } catch (Exception $e) {
                        logMessage("Erro ao atualizar método: " . $e->getMessage(), 'error');
                    }
                    break;
                    
                case 'test_generator':
                    logMessage("Testando novo gerador de números...");
                    
                    try {
                        // Simular a lógica do novo método
                        $year = date('Y');
                        $prefix = "CASE-{$year}-";
                        
                        $existingNumbers = \App\Models\LegalCase::where('case_number', 'like', $prefix . '%')
                            ->pluck('case_number')
                            ->map(function($caseNumber) use ($prefix) {
                                return (int) str_replace($prefix, '', $caseNumber);
                            })
                            ->sort()
                            ->values();
                        
                        $newNumber = 1;
                        foreach ($existingNumbers as $number) {
                            if ($number == $newNumber) {
                                $newNumber++;
                            } else {
                                break;
                            }
                        }
                        
                        $nextCaseNumber = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                        
                        logMessage("Números existentes: " . $existingNumbers->implode(', '));
                        logMessage("Próximo número será: {$nextCaseNumber}", 'success');
                        
                        // Verificar se já existe
                        $exists = \App\Models\LegalCase::where('case_number', $nextCaseNumber)->exists();
                        if ($exists) {
                            logMessage("AVISO: O número {$nextCaseNumber} já existe no banco!", 'error');
                        } else {
                            logMessage("Número {$nextCaseNumber} está disponível!", 'success');
                        }
                        
                    } catch (Exception $e) {
                        logMessage("Erro no teste: " . $e->getMessage(), 'error');
                    }
                    break;
            endswitch;
        endif;
        ?>

        <div style="margin-top: 30px;">
            <h2>🛠️ Ações Disponíveis:</h2>
            
            <a href="?action=analyze" class="btn">
                🔍 1. Analisar Casos Existentes
            </a>
            
            <a href="?action=fix_duplicates" class="btn btn-danger">
                🗑️ 2. Remover Casos Duplicados
            </a>
            
            <a href="?action=fix_generator" class="btn btn-success">
                🔧 3. Corrigir Método Generator
            </a>
            
            <a href="?action=test_generator" class="btn">
                🧪 4. Testar Novo Generator
            </a>
            
            <a href="javascript:location.reload()" class="btn">
                🔄 Atualizar Página
            </a>
            
            <a href="/cases/create" class="btn btn-success" target="_blank">
                📄 Testar Criar Caso
            </a>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <h3>📋 Instruções:</h3>
            <ol>
                <li><strong>Analisar:</strong> Primeiro clique para ver o estado atual</li>
                <li><strong>Remover Duplicados:</strong> Remove casos com números iguais</li>
                <li><strong>Corrigir Generator:</strong> Atualiza o método no código</li>
                <li><strong>Testar:</strong> Verifica se o novo método funcionará</li>
                <li><strong>Testar Criar Caso:</strong> Abre a página de criação em nova aba</li>
            </ol>
        </div>
    </div>
</body>
</html>