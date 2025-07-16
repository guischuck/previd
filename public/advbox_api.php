<?php
// Carregar configuração da API do AdvBox
require_once __DIR__ . '/advbox_api_config.php';

// Obter configurações
$config = getAdvboxConfig();
$baseUrl = $config['base_url'];
$apiKey = $config['api_key'];

// Validar configuração
$validation = validateAdvboxConfig();
if (!$validation['valid']) {
    advboxLog('Configuração inválida: ' . $validation['error'], 'error');
}

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Função para fazer requisições cURL para a API do AdvBox
function makeAdvboxRequest($endpoint, $method = 'GET', $data = null) {
    global $baseUrl, $apiKey;
    
    $url = $baseUrl . $endpoint;
    
    advboxLog("Fazendo requisição {$method} para: {$url}");
    if ($data) {
        advboxLog("Dados da requisição: " . json_encode($data));
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        advboxLog("Erro cURL: {$error}", 'error');
        return [
            'success' => false,
            'error' => 'Erro cURL: ' . $error
        ];
    }
    
    $responseData = json_decode($response, true);
    
    advboxLog("Resposta HTTP {$httpCode}: " . substr($response, 0, 200) . (strlen($response) > 200 ? '...' : ''));
    
    if ($httpCode >= 200 && $httpCode < 300) {
        advboxLog("Requisição bem-sucedida para: {$url}");
        return [
            'success' => true,
            'data' => $responseData
        ];
    } else {
        advboxLog("Erro HTTP {$httpCode} para: {$url}", 'error');
        return [
            'success' => false,
            'error' => 'HTTP ' . $httpCode . ': ' . ($responseData['message'] ?? 'Erro desconhecido'),
            'data' => $responseData
        ];
    }
}

// Função para obter usuários do AdvBox
function getAdvboxUsers() {
    $result = makeAdvboxRequest('/settings');
    
    if (!$result['success']) {
        return $result;
    }
    
    $data = $result['data'];
    $users = [];
    
    // Extrair usuários das configurações
    if (isset($data['user']) && isset($data['user']['id'])) {
        $users[] = $data['user'];
    }
    
    if (isset($data['company']) && isset($data['company']['users'])) {
        $users = array_merge($users, $data['company']['users']);
    }
    
    return [
        'success' => true,
        'data' => $users
    ];
}

// Função para obter tarefas do AdvBox
function getAdvboxTasks() {
    $result = makeAdvboxRequest('/settings');
    
    if (!$result['success']) {
        return $result;
    }
    
    $data = $result['data'];
    $tasks = [];
    
    // Extrair tarefas das configurações
    if (isset($data['tasks']) && is_array($data['tasks'])) {
        $tasks = $data['tasks'];
    }
    
    return [
        'success' => true,
        'data' => $tasks
    ];
}

// Função para buscar lawsuit por protocolo
function getLawsuitByProtocol($protocolNumber) {
    advboxLog("Buscando processo com protocolo: {$protocolNumber}");
    
    $result = makeAdvboxRequest('/lawsuits?protocol_number=' . urlencode($protocolNumber));
    
    if (!$result['success']) {
        advboxLog("Erro na requisição para AdvBox: " . $result['error'], 'error');
        return $result;
    }
    
    $lawsuits = $result['data'];
    advboxLog("Resposta do AdvBox para protocolo {$protocolNumber}: " . json_encode($lawsuits));
    
    // Verificar se $lawsuits é um array e não está vazio
    if (!is_array($lawsuits)) {
        advboxLog("Resposta não é um array: " . gettype($lawsuits), 'error');
        return [
            'success' => false,
            'error' => 'Resposta inválida do AdvBox para o protocolo: ' . $protocolNumber
        ];
    }
    
    if (empty($lawsuits)) {
        advboxLog("Nenhum processo encontrado para protocolo: {$protocolNumber}");
        return [
            'success' => false,
            'error' => 'Nenhum processo encontrado com o protocolo: ' . $protocolNumber
        ];
    }
    
    // Verificar se o primeiro elemento existe antes de acessá-lo
    if (!isset($lawsuits[0])) {
        advboxLog("Primeiro elemento não existe no array", 'error');
        return [
            'success' => false,
            'error' => 'Erro ao processar dados do processo'
        ];
    }
    
    advboxLog("Processo encontrado: " . json_encode($lawsuits[0]));
    return [
        'success' => true,
        'data' => $lawsuits[0] // Retorna o primeiro processo encontrado
    ];
}

// Função para criar tarefa no AdvBox
function createAdvboxTask($taskData) {
    // Validar campos obrigatórios
    $requiredFields = ['from', 'guests', 'tasks_id', 'lawsuits_id'];
    foreach ($requiredFields as $field) {
        if (empty($taskData[$field])) {
            return [
                'success' => false,
                'error' => 'Campo obrigatório não informado: ' . $field
            ];
        }
    }
    
    // Garantir que todos os campos obrigatórios estejam presentes
    $taskData = array_merge([
        'from' => null,
        'guests' => [],
        'tasks_id' => null,
        'lawsuits_id' => null,
        'comments' => $taskData['comments'] ?? '',
        'start_date' => date('d/m/Y'),
        'start_time' => date('H:i'),
        'end_date' => null,
        'end_time' => null,
        'date_deadline' => null,
        'local' => '',
        'urgent' => false,
        'important' => false,
        'display_schedule' => true
    ], $taskData);
    
    return makeAdvboxRequest('/posts', 'POST', $taskData);
}

// Função para criar movimento no AdvBox
function createAdvboxMovement($movementData) {
    // Validar campos obrigatórios
    $requiredFields = ['lawsuit_id', 'date', 'description'];
    foreach ($requiredFields as $field) {
        if (empty($movementData[$field])) {
            return [
                'success' => false,
                'error' => 'Campo obrigatório não informado: ' . $field
            ];
        }
    }
    
    // Garantir que todos os campos obrigatórios estejam presentes
    $movementData = array_merge([
        'lawsuit_id' => null,
        'date' => date('d/m/Y'),
        'description' => '',
        'type' => 'MANUAL'
    ], $movementData);
    
    return makeAdvboxRequest('/lawsuits/movement', 'POST', $movementData);
}

// Roteamento baseado no método HTTP e parâmetros
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Suporte a ambos formatos: /advbox_api.php/settings e /advbox_api.php?endpoint=settings
$endpoint = $_GET['endpoint'] ?? null;
if (!$endpoint) {
    // Tentar extrair da URL amigável
    if (count($pathParts) > 1 && $pathParts[0] === 'advbox_api.php') {
        $endpoint = $pathParts[1];
    }
}

// Verificar se a API key está configurada
if (!$validation['valid']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $validation['error']
    ]);
    exit();
}

try {
    switch ($method) {
        case 'GET':
            switch ($endpoint) {
                case 'settings':
                    // GET /advbox_api.php/settings - Retorna usuários e tarefas
                    $usersResult = getAdvboxUsers();
                    $tasksResult = getAdvboxTasks();
                    
                    $response = [
                        'success' => $usersResult['success'] && $tasksResult['success'],
                        'users' => $usersResult['success'] ? $usersResult['data'] : [],
                        'tasks' => $tasksResult['success'] ? $tasksResult['data'] : [],
                        'errors' => []
                    ];
                    
                    if (!$usersResult['success']) {
                        $response['errors'][] = 'Usuários: ' . $usersResult['error'];
                    }
                    
                    if (!$tasksResult['success']) {
                        $response['errors'][] = 'Tarefas: ' . $tasksResult['error'];
                    }
                    
                    echo json_encode($response);
                    break;
                    
                case 'users':
                    // GET /advbox_api.php/users - Retorna apenas usuários
                    $result = getAdvboxUsers();
                    echo json_encode($result);
                    break;
                    
                case 'tasks':
                    // GET /advbox_api.php/tasks - Retorna apenas tarefas
                    $result = getAdvboxTasks();
                    echo json_encode($result);
                    break;
                    
                case 'lawsuits':
                    // GET /advbox_api.php/lawsuits?protocol_number=123 - Busca processo por protocolo
                    $protocolNumber = $_GET['protocol_number'] ?? null;
                    
                    if (!$protocolNumber) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Parâmetro protocol_number é obrigatório'
                        ]);
                        exit();
                    }
                    
                    $result = getLawsuitByProtocol($protocolNumber);
                    echo json_encode($result);
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Endpoint não encontrado'
                    ]);
                    break;
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($endpoint) {
                case 'posts':
                    // POST /advbox_api.php/posts - Criar tarefa
                    $result = createAdvboxTask($input);
                    echo json_encode($result);
                    break;
                    
                case 'movement':
                    // POST /advbox_api.php/movement - Criar movimento
                    $result = createAdvboxMovement($input);
                    echo json_encode($result);
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Endpoint não encontrado'
                    ]);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Método não permitido'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?> 