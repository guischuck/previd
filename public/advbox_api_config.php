<?php
/**
 * Configuração da API do AdvBox
 * 
 * Este arquivo contém as configurações necessárias para a integração com a API do AdvBox.
 * As configurações podem ser sobrescritas através de variáveis de ambiente.
 */

// Configurações padrão
$config = [
    'api_key' => null,
    'base_url' => 'https://app.advbox.com.br/api/v1',
    'timeout' => 30,
    'debug' => false
];

// Tentar carregar configurações do Laravel
function loadLaravelConfig() {
    global $config;
    
    try {
        // Verificar se o Laravel está disponível
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
            
            // Inicializar Laravel apenas se necessário
            if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
                $app = require_once __DIR__ . '/../bootstrap/app.php';
                $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
                
                // Carregar configurações do Laravel
                $config['api_key'] = config('services.advbox.api_key');
                $config['base_url'] = config('services.advbox.base_url', $config['base_url']);
                
                return true;
            }
        }
    } catch (Exception $e) {
        error_log('Erro ao carregar configurações do Laravel: ' . $e->getMessage());
    }
    
    return false;
}

// Carregar configurações do Laravel
loadLaravelConfig();

// Sobrescrever com variáveis de ambiente se disponíveis
$config['api_key'] = getenv('ADVBOX_API_KEY') ?: $config['api_key'];
$config['base_url'] = getenv('ADVBOX_BASE_URL') ?: $config['base_url'];
$config['debug'] = getenv('ADVBOX_DEBUG') === 'true' ?: $config['debug'];

// Função para obter configuração
function getAdvboxConfig($key = null) {
    global $config;
    
    if ($key === null) {
        return $config;
    }
    
    return $config[$key] ?? null;
}

// Função para validar configuração
function validateAdvboxConfig() {
    $apiKey = getAdvboxConfig('api_key');
    
    if (!$apiKey) {
        return [
            'valid' => false,
            'error' => 'API key do AdvBox não configurada. Configure ADVBOX_API_KEY no .env ou no config/services.php'
        ];
    }
    
    return [
        'valid' => true,
        'config' => getAdvboxConfig()
    ];
}

// Função para logging
function advboxLog($message, $level = 'info') {
    $config = getAdvboxConfig();
    
    if ($config['debug'] || $level === 'error') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Log para arquivo se debug estiver ativado
        if ($config['debug']) {
            $logFile = __DIR__ . '/../storage/logs/advbox_api.log';
            $logDir = dirname($logFile);
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
        
        // Log para error_log se for erro
        if ($level === 'error') {
            error_log($logMessage);
        }
    }
}

// Função para obter informações de debug
function getAdvboxDebugInfo() {
    $config = getAdvboxConfig();
    $validation = validateAdvboxConfig();
    
    return [
        'config_loaded' => !empty($config['api_key']),
        'api_key_set' => !empty($config['api_key']),
        'base_url' => $config['base_url'],
        'debug_enabled' => $config['debug'],
        'validation' => $validation,
        'laravel_available' => file_exists(__DIR__ . '/../vendor/autoload.php'),
        'env_vars' => [
            'ADVBOX_API_KEY' => getenv('ADVBOX_API_KEY') ? '***' : 'não definida',
            'ADVBOX_BASE_URL' => getenv('ADVBOX_BASE_URL') ?: 'não definida',
            'ADVBOX_DEBUG' => getenv('ADVBOX_DEBUG') ?: 'não definida'
        ]
    ];
} 