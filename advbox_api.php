<?php

$apiKey = 'Cu3xUFd0EA6ZgM8RdqvLT9lYV0c1UGjONTsb2PlBZh1e2mx6pC8JdjhWHVSh';
$customerId = '2241132';
$baseUrl = 'https://app.advbox.com.br/api/v1';

// Inicializa o cURL
$ch = curl_init();

// Configura a URL com o ID do cliente
$url = $baseUrl . '/customers/' . $customerId;

// Configura as opções do cURL
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Authorization: Bearer ' . $apiKey
    ]
]);

// Executa a requisição
$response = curl_exec($ch);

// Verifica se houve algum erro
if (curl_errno($ch)) {
    echo 'Erro na requisição: ' . curl_error($ch);
    exit;
}

// Obtém o código de status HTTP
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Fecha a conexão cURL
curl_close($ch);

// Decodifica a resposta JSON
$data = json_decode($response, true);

// Verifica o código de status e exibe a resposta apropriada
if ($httpCode == 200) {
    echo "Cliente encontrado:\n";
    print_r($data);
} else {
    echo "Erro na requisição (HTTP Code: $httpCode):\n";
    print_r($data);
} 