<?php

require_once 'vendor/autoload.php';

use App\Services\AdvboxService;
use App\Models\Company;

// Buscar empresa
$company = Company::find(2);
$service = new AdvboxService($company->advbox_api_key);

echo "=== DEBUG ADVBOX API ===\n\n";

// 1. Testar configurações
echo "1. Configurações:\n";
$settings = $service->getSettings();
if ($settings['success']) {
    echo "✓ Configurações obtidas\n";
    echo "Estrutura dos dados:\n";
    print_r($settings['data']);
} else {
    echo "✗ Erro: " . $settings['error'] . "\n";
}

echo "\n\n2. Usuários:\n";
$users = $service->getUsers();
if ($users['success']) {
    echo "✓ Usuários obtidos: " . count($users['data']) . "\n";
    echo "Estrutura dos usuários:\n";
    print_r($users['data']);
} else {
    echo "✗ Erro: " . $users['error'] . "\n";
}

echo "\n\n3. Tarefas:\n";
$tasks = $service->getTasks();
if ($tasks['success']) {
    echo "✓ Tarefas obtidas: " . count($tasks['data']) . "\n";
    echo "Estrutura das tarefas (primeiras 3):\n";
    print_r(array_slice($tasks['data'], 0, 3));
} else {
    echo "✗ Erro: " . $tasks['error'] . "\n";
}

echo "\n=== FIM DEBUG ===\n"; 