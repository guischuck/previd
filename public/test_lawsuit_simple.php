<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simular resposta de sucesso
$response = [
    'success' => true,
    'data' => [
        'id' => 3441951,
        'process_number' => null,
        'protocol_number' => '1040990508',
        'folder' => null,
        'process_date' => '2025-02-14',
        'type' => 'CORREÇÃO DE CNIS',
        'responsible' => 'LAÍS DE PAULA SOUZA',
        'stage' => 'AGUARDA CONCLUSAO DO ORGAO'
    ]
];

echo json_encode($response);
exit();
?>