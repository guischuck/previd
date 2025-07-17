<?php

// Carregar o autoloader do Laravel para ter acesso às configurações
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Inicializar variáveis
$apiKey = config('services.advbox.api_key');
$baseUrl = config('services.advbox.base_url', 'https://app.advbox.com.br/api/v1');

echo "<h1>Teste de Conexão com a API do AdvBox</h1>";
echo "<p>Este script testa a conexão com a API do AdvBox sem necessidade de autenticação.</p>";

echo "<h2>Configurações</h2>";
echo "<p>API Key: " . (empty($apiKey) ? "Não configurada" : substr($apiKey, 0, 5) . "...") . "</p>";
echo "<p>Base URL: " . $baseUrl . "</p>";

// Função para buscar dados da API do AdvBox
function getAdvboxData($endpoint, $apiKey, $baseUrl) {
    echo "<h3>Testando endpoint: {$endpoint}</h3>";
    
    // Inicializar cURL
    $curl = curl_init();
    
    if ($curl === false) {
        echo "<p style='color: red;'>Falha ao inicializar cURL</p>";
        return null;
    }
    
    $url = $baseUrl . $endpoint;
    echo "<p>URL completa: {$url}</p>";
    
    // Configurar opções do cURL
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    // Configurar headers
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ];
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    // Executar a requisição
    $startTime = microtime(true);
    $response = curl_exec($curl);
    $endTime = microtime(true);
    
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $info = curl_getinfo($curl);
    
    if ($response === false) {
        $err = curl_error($curl);
        $errno = curl_errno($curl);
        echo "<p style='color: red;'>Erro cURL ({$errno}): {$err}</p>";
        curl_close($curl);
        return null;
    }
    
    curl_close($curl);
    
    echo "<p>Código HTTP: {$httpCode}</p>";
    echo "<p>Tempo de resposta: " . round(($endTime - $startTime) * 1000, 2) . " ms</p>";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color: red;'>Erro ao decodificar JSON: " . json_last_error_msg() . "</p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . "...</pre>";
            return null;
        }
        
        echo "<p style='color: green;'>Dados recebidos com sucesso!</p>";
        return $data;
    }
    
    echo "<p style='color: red;'>Erro na API: Código HTTP {$httpCode}</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    return null;
}

// Testar endpoint /settings
echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
$settingsData = getAdvboxData('/settings', $apiKey, $baseUrl);
if ($settingsData) {
    echo "<p>Estrutura da resposta:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($settingsData, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Verificar se encontrou o usuário atual
    if (isset($settingsData['user']) && isset($settingsData['user']['id'])) {
        echo "<p style='color: green;'>Usuário atual encontrado: {$settingsData['user']['name']} (ID: {$settingsData['user']['id']})</p>";
    } else {
        echo "<p style='color: red;'>Usuário atual não encontrado na resposta</p>";
    }
    
    // Verificar se encontrou usuários da empresa
    if (isset($settingsData['company']) && isset($settingsData['company']['users'])) {
        echo "<p style='color: green;'>Encontrados " . count($settingsData['company']['users']) . " usuários na empresa</p>";
    } else {
        echo "<p style='color: red;'>Usuários da empresa não encontrados na resposta</p>";
    }
}
echo "</div>";

// Testar endpoint /tasks
echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
$tasksData = getAdvboxData('/tasks', $apiKey, $baseUrl);
if ($tasksData) {
    echo "<p>Estrutura da resposta:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($tasksData, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Verificar se encontrou tarefas
    if (isset($tasksData['data']) && is_array($tasksData['data'])) {
        echo "<p style='color: green;'>Encontradas " . count($tasksData['data']) . " tarefas</p>";
    } else {
        echo "<p style='color: red;'>Tarefas não encontradas na resposta</p>";
    }
}
echo "</div>";

// Exibir instruções para corrigir problemas comuns
echo "<h2>Possíveis soluções para problemas</h2>";
echo "<ol>";
echo "<li>Verifique se a API Key está configurada corretamente no arquivo .env (ADVBOX_API_KEY)</li>";
echo "<li>Verifique se a URL base está configurada corretamente no arquivo .env (ADVBOX_BASE_URL) ou no arquivo config/services.php</li>";
echo "<li>Verifique se o servidor tem acesso à internet para fazer requisições externas</li>";
echo "<li>Verifique se o servidor tem as extensões cURL e JSON habilitadas</li>";
echo "<li>Verifique se a API do AdvBox está disponível e se a API Key tem permissão para acessar os endpoints</li>";
echo "</ol>";
