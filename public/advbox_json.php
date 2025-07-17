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

// Configuração de cabeçalhos para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verifica se o usuário está autenticado
if (!auth()->check()) {
    echo json_encode([
        'error' => 'Acesso negado. Autenticação necessária.',
        'redirect' => '/login'
    ]);
    exit;
}

// Obtém a API key do AdvBox da configuração
$apiKey = config('services.advbox.api_key');
$baseUrl = config('services.advbox.base_url', 'https://app.advbox.com.br/api/v1');

// Função para fazer a requisição para a API do AdvBox
function getAdvboxData($apiKey, $baseUrl, $endpoint = '/settings') {
    try {
        if (!$apiKey) {
            throw new Exception('API key não configurada');
        }

        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', $baseUrl . $endpoint, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true),
                'endpoint' => $endpoint,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        return [
            'success' => false,
            'error' => 'Erro ao obter dados: Código ' . $response->getStatusCode(),
            'endpoint' => $endpoint
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Erro ao obter dados: ' . $e->getMessage(),
            'endpoint' => $endpoint
        ];
    }
}

// Determina qual endpoint usar (padrão é /settings)
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '/settings';

// Obtém os dados do AdvBox
$result = getAdvboxData($apiKey, $baseUrl, $endpoint);

// Adiciona informações de debug
$result['debug'] = [
    'user' => [
        'id' => auth()->id(),
        'email' => auth()->user()->email,
        'name' => auth()->user()->name,
    ],
    'api_key_configured' => !empty($apiKey),
    'base_url' => $baseUrl,
    'server_time' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'laravel_version' => app()->version()
];

// Retorna os dados em formato JSON
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
