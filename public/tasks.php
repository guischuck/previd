<?php

// Carrega o autoloader do Laravel para ter acesso às classes do framework
require __DIR__.'/../vendor/autoload.php';

// Carrega o arquivo de bootstrap do Laravel para inicializar o framework
$app = require_once __DIR__.'/../bootstrap/app.php';

// Obtém o kernel HTTP do Laravel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Cria uma requisição HTTP a partir das variáveis globais
$request = Illuminate\Http\Request::capture();

// Inicializa o aplicativo Laravel
$response = $kernel->handle($request);

// Importa os modelos necessários
use App\Models\AdvboxTask;
use App\Models\User;

// Verifica se o usuário está autenticado
if (!auth()->check()) {
    echo '<h1>Acesso Negado</h1>';
    echo '<p>Você precisa estar autenticado para acessar esta página.</p>';
    echo '<p><a href="/login">Fazer login</a></p>';
    exit;
}

// Carregar variáveis de ambiente
require __DIR__ . '/../vendor/autoload.php';

// Inicializar variáveis
$apiKey = config('services.advbox.api_key');
$baseUrl = config('services.advbox.base_url');

// Verificar se as variáveis de ambiente estão configuradas corretamente
if (empty($apiKey)) {
    $apiKey = getenv('ADVBOX_API_KEY');
    error_log('API Key do .env: ' . substr($apiKey, 0, 5) . '...');
} else {
    error_log('API Key do config: ' . substr($apiKey, 0, 5) . '...');
}

// Garantir que temos uma URL base válida
if (empty($baseUrl)) {
    $baseUrl = 'https://app.advbox.com.br/api/v1';
    error_log('URL Base padrão: ' . $baseUrl);
} else {
    // Garantir que a URL base termina com /api/v1
    if (strpos($baseUrl, '/api/v1') === false) {
        $baseUrl = rtrim($baseUrl, '/') . '/api/v1';
    }
    error_log('URL Base do config: ' . $baseUrl);
}

// Inicializar arrays para os selects
$userOptions = [];
$tasksOptions = [];

$message = '';
$result = null;
$advboxData = null;

// Função para buscar dados diretamente da API do AdvBox usando cURL
function getAdvboxData($endpoint = '/settings') {
    global $apiKey, $baseUrl;
    
    // Exibir informações de debug na página
    echo "<div class='alert alert-warning'>Tentando buscar dados de: {$baseUrl}{$endpoint}</div>";
    
    try {
        if (!$apiKey) {
            echo "<div class='alert alert-danger'>API key não configurada!</div>";
            throw new Exception('API key não configurada');
        }
        
        echo "<div class='alert alert-info'>API Key configurada: " . substr($apiKey, 0, 5) . "...</div>";
        
        // Inicializar cURL
        $curl = curl_init();
        
        // Verificar se o cURL foi inicializado corretamente
        if ($curl === false) {
            echo "<div class='alert alert-danger'>Falha ao inicializar cURL</div>";
            throw new Exception('Falha ao inicializar cURL');
        }
        
        // Construir a URL completa
        $url = $baseUrl . $endpoint;
        echo "<div class='alert alert-info'>URL completa: {$url}</div>";
        
        // Configurar opções do cURL
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Desativar verificação SSL para testes
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        
        // Configurar headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        echo "<div class='alert alert-info'>Headers configurados: Accept, Content-Type, Authorization</div>";
        
        // Executar a requisição
        $response = curl_exec($curl);
        
        // Verificar se a requisição foi bem-sucedida
        if ($response === false) {
            $err = curl_error($curl);
            $errno = curl_errno($curl);
            echo "<div class='alert alert-danger'>Erro cURL ({$errno}): {$err}</div>";
            curl_close($curl);
            error_log("Erro cURL ({$errno}) ao obter dados da API AdvBox: {$err}");
            return null;
        }
        
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($curl);
        
        // Fechar a sessão cURL
        curl_close($curl);
        
        // Exibir informações de debug
        echo "<div class='alert alert-info'>Resposta HTTP: {$httpCode}</div>";
        echo "<div class='alert alert-info'>Tempo de resposta: {$info['total_time']} segundos</div>";
        
        if ($httpCode == 200) {
            // Verificar se a resposta é válida
            if (empty($response)) {
                echo "<div class='alert alert-danger'>Resposta vazia recebida</div>";
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "<div class='alert alert-danger'>Erro ao decodificar JSON: " . json_last_error_msg() . "</div>";
                echo "<div class='alert alert-info'>Resposta bruta: " . htmlspecialchars(substr($response, 0, 300)) . "...</div>";
                return null;
            }
            
            echo "<div class='alert alert-success'>Dados recebidos com sucesso!</div>";
            error_log('Dados recebidos diretamente da API AdvBox via cURL: ' . substr($response, 0, 200) . '...');
            return $data;
        }
        
        echo "<div class='alert alert-danger'>Erro na API: Código HTTP {$httpCode}</div>";
        echo "<div class='alert alert-info'>Resposta: " . htmlspecialchars($response) . "</div>";
        error_log('Erro ao obter dados da API AdvBox: Código HTTP ' . $httpCode . ' - Resposta: ' . $response);
        return null;
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Exceção: " . $e->getMessage() . "</div>";
        error_log('Erro ao obter dados da API AdvBox: ' . $e->getMessage());
        return null;
    }
}

// Função para enviar dados para a API do AdvBox usando cURL
function postAdvboxData($endpoint = '/posts', $data = []) {
    global $apiKey, $baseUrl;
    
    try {
        if (!$apiKey) {
            throw new Exception('API key não configurada');
        }
        
        // Inicializar cURL
        $curl = curl_init();
        
        // Configurar opções do cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
        ]);
        
        // Executar a requisição
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        // Fechar a sessão cURL
        curl_close($curl);
        
        if ($err) {
            error_log('Erro cURL ao enviar dados para API AdvBox: ' . $err);
            return [
                'success' => false,
                'error' => $err,
                'code' => 0
            ];
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            error_log('Dados enviados com sucesso para API AdvBox via cURL: ' . substr($response, 0, 200) . '...');
            return [
                'success' => true,
                'data' => $data,
                'code' => $httpCode
            ];
        }
        
        error_log('Erro ao enviar dados para API AdvBox: Código HTTP ' . $httpCode . ' - Resposta: ' . $response);
        return [
            'success' => false,
            'error' => $data['message'] ?? 'Erro desconhecido',
            'data' => $data,
            'code' => $httpCode
        ];
    } catch (Exception $e) {
        error_log('Erro ao enviar dados para API AdvBox: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'code' => 0
        ];
    }
}

// Usar o serviço AdvboxService do Laravel para buscar dados
$advboxService = app()->make('App\Services\AdvboxService');

// Buscar dados da API do AdvBox para preencher os selects
try {
    // Buscar configurações do usuário para obter os dados de usuários
    $settingsResponse = $advboxService->getSettings();
    
    if ($settingsResponse['success']) {
        $settingsData = $settingsResponse['data'];
        
        // Extrair usuário atual para o select de remetente
        if (isset($settingsData['user']) && isset($settingsData['user']['id'])) {
            $userOptions[$settingsData['user']['id']] = $settingsData['user']['name'] . ' (ID: ' . $settingsData['user']['id'] . ')';
        }
        
        // Extrair usuários da empresa para o select de convidados
        if (isset($settingsData['company']) && isset($settingsData['company']['users'])) {
            foreach ($settingsData['company']['users'] as $user) {
                if (isset($user['id']) && isset($user['name'])) {
                    $userOptions[$user['id']] = $user['name'] . ' (ID: ' . $user['id'] . ')';
                }
            }
        }
    } else {
        error_log('Erro ao obter configurações da API AdvBox: ' . ($settingsResponse['error'] ?? 'Erro desconhecido'));
    }
    
    // Buscar tarefas existentes para o select de tarefas
    $tasksResponse = $advboxService->getTasks();
    
    if ($tasksResponse['success']) {
        $tasksData = $tasksResponse['data'];
        
        if (isset($tasksData['data']) && is_array($tasksData['data'])) {
            foreach ($tasksData['data'] as $task) {
                if (isset($task['id']) && isset($task['title'])) {
                    $tasksOptions[$task['id']] = $task['title'] . ' (ID: ' . $task['id'] . ')';
                }
            }
        }
    } else {
        error_log('Erro ao obter tarefas da API AdvBox: ' . ($tasksResponse['error'] ?? 'Erro desconhecido'));
    }
    
} catch (Exception $e) {
    error_log('Erro ao buscar dados do AdvBox: ' . $e->getMessage());
    $message = '<div class="alert alert-warning">Aviso: Não foi possível carregar dados do AdvBox. ' . $e->getMessage() . '</div>';
}

$formData = [
    'from' => isset($advboxData['user']['id']) ? $advboxData['user']['id'] : '',
    'guests' => '',
    'tasks_id' => '',
    'lawsuits_id' => '',
    'comments' => '',
    'start_date' => date('d/m/Y'),
    'start_time' => date('H:i'),
    'end_date' => date('d/m/Y'),
    'end_time' => date('H:i', strtotime('+1 hour')),
    'date_deadline' => date('d/m/Y', strtotime('+7 days')),
    'local' => '',
    'urgent' => false,
    'important' => false,
    'display_schedule' => true,
    'date' => date('d/m/Y'),
    'folder' => '',
    'protocol_number' => '',
    'process_number' => ''
];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados do formulário
    $formData = [
        'from' => filter_input(INPUT_POST, 'from', FILTER_SANITIZE_NUMBER_INT),
        'guests' => filter_input(INPUT_POST, 'guests', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [],
        'tasks_id' => filter_input(INPUT_POST, 'tasks_id', FILTER_SANITIZE_NUMBER_INT),
        'lawsuits_id' => filter_input(INPUT_POST, 'lawsuits_id', FILTER_SANITIZE_NUMBER_INT),
        'comments' => filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING),
        'start_date' => filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING),
        'start_time' => filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING),
        'end_date' => filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING),
        'end_time' => filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING),
        'date_deadline' => filter_input(INPUT_POST, 'date_deadline', FILTER_SANITIZE_STRING),
        'local' => filter_input(INPUT_POST, 'local', FILTER_SANITIZE_STRING),
        'folder' => filter_input(INPUT_POST, 'folder', FILTER_SANITIZE_STRING),
        'protocol' => filter_input(INPUT_POST, 'protocol', FILTER_SANITIZE_STRING),
        'process_number' => filter_input(INPUT_POST, 'process_number', FILTER_SANITIZE_STRING),
        'urgent' => filter_input(INPUT_POST, 'urgent', FILTER_VALIDATE_BOOLEAN),
        'important' => filter_input(INPUT_POST, 'important', FILTER_VALIDATE_BOOLEAN),
        'display_schedule' => filter_input(INPUT_POST, 'display_schedule', FILTER_VALIDATE_BOOLEAN)
    ];
    
    // Remover campos vazios
    $formData = array_filter($formData, function($value) {
        return $value !== null && $value !== '' && $value !== false;
    });
    
    // Fazer a requisição para a API do AdvBox usando cURL
    $result = postAdvboxData('/posts', $formData);
    
    if ($result['success']) {
        $responseData = $result['data'];
        $message = '<div class="alert alert-success">Tarefa criada com sucesso! ID: ' . ($responseData['id'] ?? 'N/A') . '</div>';
        
        // Exibir detalhes da resposta para debug
        $message .= '<div class="alert alert-info">Resposta da API: <pre>' . json_encode($responseData, JSON_PRETTY_PRINT) . '</pre></div>';
    } else {
        $errorMessage = $result['error'];
        $message = '<div class="alert alert-danger">Erro ao criar tarefa: ' . $errorMessage . ' (Código: ' . $result['code'] . ')</div>';
        
        // Exibir detalhes do erro para debug
        if (isset($result['data'])) {
            $message .= '<div class="alert alert-warning">Detalhes do erro: <pre>' . json_encode($result['data'], JSON_PRETTY_PRINT) . '</pre></div>';
        }
    }
}

// Estilo CSS para a página
$css = <<<CSS
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9fafb;
    }
    h1 {
        color: #2563eb;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 10px;
    }
    .card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    input[type="text"],
    input[type="date"],
    input[type="time"],
    textarea,
    select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 16px;
    }
    textarea {
        height: 100px;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .checkbox-group input[type="checkbox"] {
        margin-right: 5px;
    }
    .btn {
        display: inline-block;
        background-color: #2563eb;
        color: white;
        padding: 10px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    .btn:hover {
        background-color: #1d4ed8;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .alert-success {
        background-color: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }
    .alert-danger {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }
    .form-col {
        flex: 1;
    }
    .result-container {
        background-color: #f3f4f6;
        padding: 15px;
        border-radius: 8px;
        overflow-x: auto;
        margin-top: 20px;
    }
    pre {
        margin: 0;
        white-space: pre-wrap;
    }
</style>
CSS;

// HTML da página
echo '<!DOCTYPE html>';
echo '<html lang="pt-BR">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Criar Tarefa - AdvBox - Previdia</title>';
echo $css;
echo '</head>';
echo '<body>';

echo '<div class="header">';
echo '<h1>Criar Nova Tarefa - AdvBox</h1>';
echo '<div>';
echo '<a href="/dashboard" class="btn" style="margin-right: 10px;">Voltar ao Dashboard</a>';
echo '<a href="/advbox_json.php" class="btn" style="background-color: #4b5563;">Ver Dados JSON</a>';
echo '<a href="/tasks.php" class="btn" style="background-color: #10b981; margin-left: 10px;">Atualizar Página</a>';
echo '</div>';
echo '</div>';

// Exibir informações sobre a fonte dos dados
if (!empty($userOptions) || !empty($tasksOptions)) {
    echo '<div class="alert alert-info">Dados carregados diretamente da API AdvBox via cURL. Usuários: ' . count($userOptions) . ', Tarefas: ' . count($tasksOptions) . '</div>';
}

// Exibe mensagem de resultado se houver
if ($message) {
    echo $message;
}

echo '<div class="card">';
echo '<form method="POST" action="">';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="from">Remetente (from):</label>';
echo '<select id="from" name="from" class="form-control">';
if (empty($userOptions)) {
    echo '<option value="">Nenhum usuário encontrado</option>';
} else {
    echo '<option value="">Selecione um usuário</option>';
    foreach ($userOptions as $id => $name) {
        echo '<option value="' . $id . '">' . htmlspecialchars($name) . '</option>';
    }
}
echo '</select>';
echo '<small>' . count($userOptions) . ' usuários encontrados</small>';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="guests">Convidados:</label>';
echo '<select id="guests" name="guests[]" multiple class="form-control" style="height: 100px;">';
if (!empty($userOptions)) {
    foreach ($userOptions as $id => $name) {
        echo '<option value="' . $id . '">' . htmlspecialchars($name) . '</option>';
    }
} else {
    echo '<option value="">Carregue a página novamente para ver os usuários</option>';
}
echo '</select>';
echo '<small>Segure Ctrl para selecionar múltiplos usuários</small>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="tasks_id">Tarefa (tasks_id):</label>';
echo '<select id="tasks_id" name="tasks_id" class="form-control">';
echo '<option value="">Selecione uma tarefa ou deixe em branco para nova</option>';
if (!empty($tasksOptions)) {
    foreach ($tasksOptions as $id => $title) {
        echo '<option value="' . $id . '">' . htmlspecialchars($title) . '</option>';
    }
} else {
    echo '<option value="" disabled>Nenhuma tarefa encontrada</option>';
}
echo '</select>';
echo '<small>' . count($tasksOptions) . ' tarefas encontradas</small>';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="lawsuits_id">ID do Processo (lawsuits_id):</label>';
echo '<input type="text" id="lawsuits_id" name="lawsuits_id" value="">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="comments">Comentários:</label>';
echo '<textarea id="comments" name="comments"></textarea>';
echo '</div>';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="start_date">Data de Início:</label>';
echo '<input type="text" id="start_date" name="start_date" value="" placeholder="DD/MM/AAAA">';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="start_time">Hora de Início:</label>';
echo '<input type="text" id="start_time" name="start_time" value="" placeholder="HH:MM">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="end_date">Data de Término:</label>';
echo '<input type="text" id="end_date" name="end_date" value="" placeholder="DD/MM/AAAA">';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="end_time">Hora de Término:</label>';
echo '<input type="text" id="end_time" name="end_time" value="" placeholder="HH:MM">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="date_deadline">Data Limite:</label>';
echo '<input type="text" id="date_deadline" name="date_deadline" value="" placeholder="DD/MM/AAAA">';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="date">Data:</label>';
echo '<input type="text" id="date" name="date" value="" placeholder="DD/MM/AAAA">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="local">Local:</label>';
echo '<input type="text" id="local" name="local" value="" placeholder="Ex: Sala de reuniões - 3º andar">';
echo '</div>';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="folder">Pasta:</label>';
echo '<input type="text" id="folder" name="folder" value="" placeholder="Ex: Pasta 123">';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group">';
echo '<label for="protocol_number">Número de Protocolo:</label>';
echo '<input type="text" id="protocol_number" name="protocol_number" value="" placeholder="Ex: PROT-2025-001">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="process_number">Número do Processo:</label>';
echo '<input type="text" id="process_number" name="process_number" value="" placeholder="Ex: 0123456-78.2025.8.26.0100">';
echo '</div>';

echo '<div class="form-row">';
echo '<div class="form-col">';
echo '<div class="form-group checkbox-group">';
echo '<input type="checkbox" id="urgent" name="urgent" value="1">';
echo '<label for="urgent">Urgente</label>';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group checkbox-group">';
echo '<input type="checkbox" id="important" name="important" value="1">';
echo '<label for="important">Importante</label>';
echo '</div>';
echo '</div>';

echo '<div class="form-col">';
echo '<div class="form-group checkbox-group">';
echo '<input type="checkbox" id="display_schedule" name="display_schedule" value="1">';
echo '<label for="display_schedule">Exibir na Agenda</label>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<button type="submit" class="btn">Criar Tarefa</button>';
echo '</form>';
echo '</div>';

// Exibe o resultado da API se houver
if ($result) {
    echo '<div class="card">';
    echo '<h2>Resposta da API</h2>';
    echo '<div class="result-container">';
    echo '<pre>' . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
    echo '</div>';
    echo '</div>';
}


echo '</body>';
echo '</html>';
