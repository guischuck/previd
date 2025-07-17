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

// Verifica se o usuário está autenticado
if (!auth()->check()) {
    echo '<h1>Acesso Negado</h1>';
    echo '<p>Você precisa estar autenticado para acessar esta página.</p>';
    echo '<p><a href="/login">Fazer login</a></p>';
    exit;
}

// Obtém a API key do AdvBox da configuração
$apiKey = config('services.advbox.api_key');
$baseUrl = config('services.advbox.base_url', 'https://app.advbox.com.br/api/v1');

// Função para fazer a requisição para a API do AdvBox
function getAdvboxSettings($apiKey, $baseUrl) {
    try {
        if (!$apiKey) {
            throw new Exception('API key não configurada');
        }

        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', $baseUrl . '/settings', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $responseData = json_decode($response->getBody(), true);
            
            // Log para debug
            error_log('AdvBox API Response: ' . print_r($responseData, true));
            
            return [
                'success' => true,
                'data' => $responseData
            ];
        }

        return [
            'success' => false,
            'error' => 'Erro ao obter configurações: Código ' . $response->getStatusCode()
        ];

    } catch (Exception $e) {
        error_log('AdvBox API Error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Erro ao obter configurações: ' . $e->getMessage()
        ];
    }
}

// Obtém as configurações do AdvBox
$advboxSettings = getAdvboxSettings($apiKey, $baseUrl);

// Estilo CSS básico para a página
$css = <<<CSS
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
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
    .card-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card-content {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .field {
        margin-bottom: 15px;
    }
    .field-label {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 5px;
    }
    .field-value {
        font-weight: 500;
    }
    .alert {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 15px;
        color: #b91c1c;
        margin-bottom: 20px;
    }
    .badge {
        display: inline-block;
        background-color: #e5e7eb;
        border-radius: 9999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    .badge-container {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .btn {
        display: inline-block;
        background-color: #2563eb;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
    }
    .btn:hover {
        background-color: #1d4ed8;
    }
    pre {
        background-color: #f3f4f6;
        padding: 15px;
        border-radius: 8px;
        overflow-x: auto;
    }
</style>
CSS;

// HTML da página
echo '<!DOCTYPE html>';
echo '<html lang="pt-BR">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Usuários AdvBox - Previdia</title>';
echo $css;
echo '</head>';
echo '<body>';

echo '<div class="header">';
echo '<h1>Configurações de Usuário - AdvBox</h1>';
echo '<a href="/dashboard" class="btn">Voltar ao Dashboard</a>';
echo '</div>';

// Exibe mensagem de erro se houver
if (!$advboxSettings['success']) {
    echo '<div class="alert">';
    echo '<strong>Erro:</strong> ' . $advboxSettings['error'];
    echo '</div>';
    
    // Exibe informações de debug para ajudar na resolução do problema
    echo '<div class="card">';
    echo '<div class="card-title">Informações de Debug</div>';
    echo '<div class="field">';
    echo '<div class="field-label">API Key configurada?</div>';
    echo '<div class="field-value">' . ($apiKey ? 'Sim' : 'Não') . '</div>';
    echo '</div>';
    echo '<div class="field">';
    echo '<div class="field-label">Base URL</div>';
    echo '<div class="field-value">' . $baseUrl . '</div>';
    echo '</div>';
    echo '</div>';
} else {
    // Exibe as informações do usuário
    $data = $advboxSettings['data'];
    
    // Informações do usuário
    echo '<div class="card">';
    echo '<div class="card-title">Informações do Usuário</div>';
    echo '<div class="card-content">';
    
    echo '<div>';
    echo '<div class="field">';
    echo '<div class="field-label">Nome</div>';
    echo '<div class="field-value">' . (isset($data['name']) ? $data['name'] : 'Não informado') . '</div>';
    echo '</div>';
    
    echo '<div class="field">';
    echo '<div class="field-label">Email</div>';
    echo '<div class="field-value">' . (isset($data['email']) ? $data['email'] : 'Não informado') . '</div>';
    echo '</div>';
    
    echo '<div class="field">';
    echo '<div class="field-label">Telefone</div>';
    echo '<div class="field-value">' . (isset($data['phone']) ? $data['phone'] : 'Não informado') . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div>';
    echo '<div class="field">';
    echo '<div class="field-label">Função</div>';
    echo '<div class="field-value">' . (isset($data['role']['name']) ? $data['role']['name'] : 'Não informado') . '</div>';
    echo '</div>';
    
    echo '<div class="field">';
    echo '<div class="field-label">Criado em</div>';
    echo '<div class="field-value">' . (isset($data['created_at']) ? date('d/m/Y H:i', strtotime($data['created_at'])) : 'Não informado') . '</div>';
    echo '</div>';
    
    echo '<div class="field">';
    echo '<div class="field-label">Atualizado em</div>';
    echo '<div class="field-value">' . (isset($data['updated_at']) ? date('d/m/Y H:i', strtotime($data['updated_at'])) : 'Não informado') . '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    // Informações da empresa
    if (isset($data['company'])) {
        echo '<div class="card">';
        echo '<div class="card-title">Informações da Empresa</div>';
        echo '<div class="card-content">';
        
        echo '<div>';
        echo '<div class="field">';
        echo '<div class="field-label">Nome</div>';
        echo '<div class="field-value">' . $data['company']['name'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Documento</div>';
        echo '<div class="field-value">' . $data['company']['document'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Endereço</div>';
        echo '<div class="field-value">' . $data['company']['address'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Cidade/Estado</div>';
        echo '<div class="field-value">' . $data['company']['city'] . '/' . $data['company']['state'] . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div>';
        echo '<div class="field">';
        echo '<div class="field-label">CEP</div>';
        echo '<div class="field-value">' . $data['company']['zip_code'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Telefone</div>';
        echo '<div class="field-value">' . $data['company']['phone'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Email</div>';
        echo '<div class="field-value">' . $data['company']['email'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Website</div>';
        echo '<div class="field-value">' . ($data['company']['website'] ?: 'Não informado') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    // Informações da assinatura
    if (isset($data['subscription'])) {
        echo '<div class="card">';
        echo '<div class="card-title">Informações da Assinatura</div>';
        echo '<div class="card-content">';
        
        echo '<div>';
        echo '<div class="field">';
        echo '<div class="field-label">Nome</div>';
        echo '<div class="field-value">' . $data['subscription']['name'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Descrição</div>';
        echo '<div class="field-value">' . $data['subscription']['description'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Preço</div>';
        echo '<div class="field-value">R$ ' . number_format($data['subscription']['price'], 2, ',', '.') . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Ciclo de Cobrança</div>';
        echo '<div class="field-value">' . $data['subscription']['billing_cycle'] . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div>';
        echo '<div class="field">';
        echo '<div class="field-label">Dias de Teste</div>';
        echo '<div class="field-value">' . $data['subscription']['trial_days'] . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Status</div>';
        echo '<div class="field-value">' . ($data['subscription']['active'] ? 'Ativo' : 'Inativo') . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Criado em</div>';
        echo '<div class="field-value">' . date('d/m/Y', strtotime($data['subscription']['created_at'])) . '</div>';
        echo '</div>';
        
        echo '<div class="field">';
        echo '<div class="field-label">Expira em</div>';
        echo '<div class="field-value">' . (isset($data['subscription']['expires_at']) ? date('d/m/Y', strtotime($data['subscription']['expires_at'])) : 'Não expira') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Recursos da assinatura
        if (!empty($data['subscription']['features'])) {
            echo '<div class="field">';
            echo '<div class="field-label">Recursos Incluídos</div>';
            echo '<div class="badge-container">';
            foreach ($data['subscription']['features'] as $feature) {
                echo '<span class="badge">' . $feature . '</span>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    // Permissões do usuário
    if (!empty($data['permissions'])) {
        echo '<div class="card">';
        echo '<div class="card-title">Permissões do Usuário</div>';
        echo '<div class="badge-container">';
        foreach ($data['permissions'] as $permission) {
            echo '<span class="badge">' . $permission . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }
    
    // Exibe os dados brutos em formato JSON para debug (opcional)
    echo '<div class="card">';
    echo '<div class="card-title">Dados Brutos (Debug)</div>';
    echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
    echo '</div>';
}

echo '</body>';
echo '</html>';
