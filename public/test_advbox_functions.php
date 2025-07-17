<?php
header('Content-Type: application/json');
require_once __DIR__ . '/advbox_api_config.php';

// Função para fazer requisições cURL para a API do AdvBox
function makeAdvboxRequest($endpoint, $method = 'GET', $data = null) {
    $config = getAdvboxConfig();
    $baseUrl = $config['base_url'];
    $apiKey = $config['api_key'];
    
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
    
    advboxLog("Resposta HTTP {$httpCode}: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : ''));
    
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

// Testar requisição para /settings
$result = makeAdvboxRequest('/settings');

echo json_encode([
    'success' => $result['success'],
    'error' => $result['error'] ?? null,
    'data_type' => gettype($result['data'] ?? null),
    'data_keys' => is_array($result['data'] ?? null) ? array_keys($result['data']) : null,
    'raw_response' => $result['data'] ?? null
], JSON_PRETTY_PRINT);
?>