<?php

require_once 'vendor/autoload.php';
use App\Services\PythonCNISExtractorService;

// Configura o Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DIAGNÓSTICO PYTHON VPS ===\n\n";

// 1. Verificar caminhos e arquivos
echo "1. 📁 Verificando caminhos dos arquivos:\n";
$scriptPath = base_path('simple_cnis_extractor.py');
echo "   Script Python: $scriptPath\n";
echo "   Arquivo existe: " . (file_exists($scriptPath) ? "✅ SIM" : "❌ NÃO") . "\n";

if (file_exists($scriptPath)) {
    $fileSize = filesize($scriptPath);
    $lastModified = date('Y-m-d H:i:s', filemtime($scriptPath));
    echo "   Tamanho: $fileSize bytes\n";
    echo "   Última modificação: $lastModified\n";
}

// 2. Verificar conteúdo do arquivo
echo "\n2. 🔍 Verificando se o arquivo contém as correções:\n";
if (file_exists($scriptPath)) {
    $content = file_get_contents($scriptPath);
    
    // Verifica se contém as correções para CNPJs incompletos
    if (strpos($content, 'CNPJ incompleto') !== false) {
        echo "   ✅ Contém correções para CNPJ incompleto\n";
    } else {
        echo "   ❌ NÃO contém correções para CNPJ incompleto\n";
    }
    
    // Verifica se contém correções para Indeterminado
    if (strpos($content, 'Indeterminado') !== false) {
        echo "   ✅ Contém correções para vínculos Indeterminado\n";
    } else {
        echo "   ❌ NÃO contém correções para vínculos Indeterminado\n";
    }
    
    // Verifica se contém filtros para excluir benefícios
    if (strpos($content, 'AUXILIO\s+DOENCA|APOSENTADORIA|BENEFICIO') !== false) {
        echo "   ✅ Contém filtros para excluir benefícios\n";
    } else {
        echo "   ❌ NÃO contém filtros para excluir benefícios\n";
    }
    
    // Conta quantas linhas tem o arquivo
    $lines = substr_count($content, "\n") + 1;
    echo "   📄 Total de linhas: $lines\n";
}

// 3. Testar execução do Python
echo "\n3. 🐍 Testando execução do Python:\n";
$service = new PythonCNISExtractorService();

// Verificar ambiente
$environment = $service->checkPythonEnvironment();
echo "   Python executável: " . ($environment['python_executable'] ? "✅ OK" : "❌ ERRO") . "\n";
echo "   Script Python: " . ($environment['python_script'] ? "✅ OK" : "❌ ERRO") . "\n";

if (isset($environment['python_version'])) {
    echo "   Versão Python: {$environment['python_version']}\n";
}

// Verificar módulos
echo "   Módulos Python:\n";
foreach ($environment['required_modules'] as $module => $available) {
    echo "      " . ($available ? "✅" : "❌") . " $module\n";
}

// 4. Testar com um PDF se disponível
echo "\n4. 📄 Testando processamento de PDF:\n";
$testFiles = [
    'app/Services/cnis 1.pdf',
    'app/Services/CNIS.pdf',
    'CNIS.pdf',
    'cnis 1.pdf'
];

$testFile = null;
foreach ($testFiles as $file) {
    if (file_exists($file)) {
        $testFile = $file;
        break;
    }
}

if ($testFile) {
    echo "   📄 Arquivo de teste encontrado: $testFile\n";
    echo "   ⏳ Processando...\n";
    
    $result = $service->processCNIS($testFile);
    
    if ($result['success']) {
        $vinculos = $result['data']['vinculos_empregaticios'] ?? [];
        echo "   ✅ Processamento bem-sucedido!\n";
        echo "   📊 Vínculos encontrados: " . count($vinculos) . "\n";
        echo "   👤 Nome: " . ($result['data']['client_name'] ?? 'N/A') . "\n";
        echo "   📋 CPF: " . ($result['data']['client_cpf'] ?? 'N/A') . "\n";
        
        // Mostra alguns vínculos como exemplo
        if (count($vinculos) > 0) {
            echo "   📋 Primeiros vínculos:\n";
            for ($i = 0; $i < min(3, count($vinculos)); $i++) {
                $vinculo = $vinculos[$i];
                echo "      " . ($i + 1) . ". {$vinculo['empregador']} (CNPJ: {$vinculo['cnpj']})\n";
            }
        }
    } else {
        echo "   ❌ Erro no processamento: {$result['error']}\n";
    }
} else {
    echo "   ❌ Nenhum arquivo de teste encontrado\n";
}

// 5. Verificar versão do arquivo atual
echo "\n5. 🔍 Verificando versão do arquivo atual:\n";
if (file_exists($scriptPath)) {
    $firstLines = array_slice(file($scriptPath), 0, 10);
    echo "   📄 Primeiras linhas do arquivo:\n";
    foreach ($firstLines as $i => $line) {
        echo "      " . ($i + 1) . ". " . trim($line) . "\n";
    }
}

echo "\n=== DIAGNÓSTICO CONCLUÍDO ===\n";
echo "\nSe o problema persistir, execute os seguintes comandos no VPS:\n";
echo "1. Verificar se o arquivo foi atualizado: ls -la simple_cnis_extractor.py\n";
echo "2. Limpar cache Python: find . -name '*.pyc' -delete\n";
echo "3. Reiniciar serviços: sudo supervisorctl restart all\n";
echo "4. Atualizar dependências: pip install -r requirements_simple.txt --upgrade\n";
echo "5. Testar manualmente: python3 simple_cnis_extractor.py --help\n"; 