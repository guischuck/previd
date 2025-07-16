<?php
/**
 * Exemplo de uso da Nova API do AdvBox
 * 
 * Este arquivo demonstra como usar a nova API em PHP puro para integração com o AdvBox.
 */

// Incluir a configuração
require_once __DIR__ . '/advbox_api_config.php';

// Verificar se a configuração está válida
$validation = validateAdvboxConfig();
if (!$validation['valid']) {
    die("Erro de configuração: " . $validation['error']);
}

// Função para fazer requisições para a API
function callAdvboxApi($endpoint, $method = 'GET', $data = null) {
    $url = "http://{$_SERVER['HTTP_HOST']}/advbox_api.php{$endpoint}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Exemplos de uso
$examples = [];

// 1. Buscar configurações (usuários e tarefas)
$examples['config'] = callAdvboxApi('/settings');

// 2. Buscar apenas usuários
$examples['users'] = callAdvboxApi('/users');

// 3. Buscar apenas tarefas
$examples['tasks'] = callAdvboxApi('/tasks');

// 4. Buscar processo por protocolo (exemplo)
$examples['lawsuit'] = callAdvboxApi('/lawsuits?protocol_number=123456789');

// 5. Exemplo de criação de tarefa (comentado para não criar tarefas reais)
/*
$taskData = [
    'from' => '1',
    'guests' => ['1'],
    'tasks_id' => '1',
    'lawsuits_id' => '123',
    'comments' => 'Tarefa criada via exemplo',
    'start_date' => date('d/m/Y'),
    'start_time' => '09:00',
    'end_date' => date('d/m/Y'),
    'end_time' => '17:00',
    'date_deadline' => date('d/m/Y'),
    'local' => '',
    'urgent' => false,
    'important' => false,
    'display_schedule' => true
];
$examples['create_task'] = callAdvboxApi('/posts', 'POST', $taskData);
*/

// 6. Exemplo de criação de movimento (comentado para não criar movimentos reais)
/*
$movementData = [
    'lawsuit_id' => '123',
    'date' => date('d/m/Y'),
    'description' => 'Movimento criado via exemplo',
    'type' => 'MANUAL'
];
$examples['create_movement'] = callAdvboxApi('/movement', 'POST', $movementData);
*/

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemplo de Uso - Nova API AdvBox</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .example-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .success { 
            background-color: #d4edda; 
            border-color: #c3e6cb; 
        }
        .error { 
            background-color: #f8d7da; 
            border-color: #f5c6cb; 
        }
        .info { 
            background-color: #d1ecf1; 
            border-color: #bee5eb; 
        }
        pre { 
            background-color: #f8f9fa; 
            padding: 10px; 
            border-radius: 3px; 
            overflow-x: auto;
            font-size: 12px;
        }
        h1, h2, h3 { color: #333; }
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .code-block {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 3px;
            padding: 15px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Exemplo de Uso - Nova API AdvBox</h1>
        <p>Este arquivo demonstra como usar a nova API em PHP puro para integração com o AdvBox.</p>

        <div class="example-section info">
            <h2>1. Informações de Configuração</h2>
            <p><strong>Status:</strong> <span class="status-success">✓ Configuração válida</span></p>
            <p><strong>Base URL:</strong> <?php echo getAdvboxConfig('base_url'); ?></p>
            <p><strong>Debug:</strong> <?php echo getAdvboxConfig('debug') ? 'Ativado' : 'Desativado'; ?></p>
        </div>

        <div class="example-section info">
            <h2>2. Buscar Configurações (Usuários e Tarefas)</h2>
            <p><strong>Endpoint:</strong> <code>GET /advbox_api.php/settings</code></p>
            <p><strong>Status:</strong> 
                <span class="<?php echo $examples['config']['status'] === 200 ? 'status-success' : 'status-error'; ?>">
                    HTTP <?php echo $examples['config']['status']; ?>
                </span>
            </p>
            <pre><?php echo json_encode($examples['config']['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
        </div>

        <div class="example-section info">
            <h2>3. Buscar Usuários</h2>
            <p><strong>Endpoint:</strong> <code>GET /advbox_api.php/users</code></p>
            <p><strong>Status:</strong> 
                <span class="<?php echo $examples['users']['status'] === 200 ? 'status-success' : 'status-error'; ?>">
                    HTTP <?php echo $examples['users']['status']; ?>
                </span>
            </p>
            <pre><?php echo json_encode($examples['users']['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
        </div>

        <div class="example-section info">
            <h2>4. Buscar Tarefas</h2>
            <p><strong>Endpoint:</strong> <code>GET /advbox_api.php/tasks</code></p>
            <p><strong>Status:</strong> 
                <span class="<?php echo $examples['tasks']['status'] === 200 ? 'status-success' : 'status-error'; ?>">
                    HTTP <?php echo $examples['tasks']['status']; ?>
                </span>
            </p>
            <pre><?php echo json_encode($examples['tasks']['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
        </div>

        <div class="example-section info">
            <h2>5. Buscar Processo por Protocolo</h2>
            <p><strong>Endpoint:</strong> <code>GET /advbox_api.php/lawsuits?protocol_number=123456789</code></p>
            <p><strong>Status:</strong> 
                <span class="<?php echo $examples['lawsuit']['status'] === 200 ? 'status-success' : 'status-error'; ?>">
                    HTTP <?php echo $examples['lawsuit']['status']; ?>
                </span>
            </p>
            <pre><?php echo json_encode($examples['lawsuit']['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
        </div>

        <div class="example-section info">
            <h2>6. Exemplo de Código PHP</h2>
            <p>Aqui está um exemplo de como usar a API em seu código PHP:</p>
            
            <div class="code-block">
// Incluir a configuração
require_once 'advbox_api_config.php';

// Verificar configuração
$validation = validateAdvboxConfig();
if (!$validation['valid']) {
    die("Erro: " . $validation['error']);
}

// Função para fazer requisições
function callAdvboxApi($endpoint, $method = 'GET', $data = null) {
    $url = "http://seu-dominio.com/advbox_api.php{$endpoint}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Exemplos de uso
$settings = callAdvboxApi('/settings');
$users = callAdvboxApi('/users');
$tasks = callAdvboxApi('/tasks');
$lawsuit = callAdvboxApi('/lawsuits?protocol_number=123456789');

// Criar tarefa
$taskData = [
    'from' => '1',
    'guests' => ['1'],
    'tasks_id' => '1',
    'lawsuits_id' => '123',
    'comments' => 'Nova tarefa',
    'start_date' => date('d/m/Y'),
    'start_time' => '09:00',
    'end_date' => date('d/m/Y'),
    'end_time' => '17:00'
];

$result = callAdvboxApi('/posts', 'POST', $taskData);
            </div>
        </div>

        <div class="example-section info">
            <h2>7. Exemplo de Código JavaScript</h2>
            <p>Aqui está um exemplo de como usar a API em JavaScript:</p>
            
            <div class="code-block">
// Função para fazer requisições
async function callAdvboxApi(endpoint, method = 'GET', data = null) {
    const url = `/advbox_api.php${endpoint}`;
    
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    const response = await fetch(url, options);
    const result = await response.json();
    
    return {
        success: response.ok,
        data: result,
        status: response.status
    };
}

// Exemplos de uso
const settings = await callAdvboxApi('/settings');
const users = await callAdvboxApi('/users');
const tasks = await callAdvboxApi('/tasks');
const lawsuit = await callAdvboxApi('/lawsuits?protocol_number=123456789');

// Criar tarefa
const taskData = {
    from: "1",
    guests: ["1"],
    tasks_id: "1",
    lawsuits_id: "123",
    comments: "Nova tarefa",
    start_date: new Date().toLocaleDateString('pt-BR'),
    start_time: "09:00",
    end_date: new Date().toLocaleDateString('pt-BR'),
    end_time: "17:00"
};

const result = await callAdvboxApi('/posts', 'POST', taskData);
            </div>
        </div>

        <div class="example-section info">
            <h2>8. Links Úteis</h2>
            <ul>
                <li><a href="/test_advbox_new_api.php" target="_blank">Teste Interativo da API</a></li>
                <li><a href="/advbox_api_config.php" target="_blank">Informações de Configuração</a></li>
                <li><a href="/ADVBOX_API_README.md" target="_blank">Documentação Completa</a></li>
            </ul>
        </div>
    </div>
</body>
</html> 