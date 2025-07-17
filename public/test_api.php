<?php
header('Content-Type: application/json');

// Teste simples da API
require_once __DIR__ . '/advbox_api_config.php';

$config = getAdvboxConfig();
$validation = validateAdvboxConfig();

echo json_encode([
    'config_valid' => $validation['valid'],
    'api_key_set' => !empty($config['api_key']),
    'base_url' => $config['base_url'],
    'debug' => $config['debug'],
    'error' => $validation['error'] ?? null
], JSON_PRETTY_PRINT);
?>