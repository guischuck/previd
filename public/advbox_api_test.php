<?php
// Test version of AdvBox API that returns mock data
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? null;

try {
    switch ($method) {
        case 'GET':
            switch ($endpoint) {
                case 'settings':
                    // Mock data for testing
                    $response = [
                        'success' => true,
                        'users' => [
                            ['id' => 165892, 'name' => 'AD INTEGRAÇÃO', 'email' => 'AD@KOETZADVOCACIA.COM.BR'],
                            ['id' => 125310, 'name' => 'ADRIELLY THALITA SANTOS', 'email' => 'ADRIELLYTHALITASANTOS@GMAIL.COM'],
                            ['id' => 117643, 'name' => 'ALLEF DANILO', 'email' => 'ALLEFDANILO.SA@GMAIL.COM']
                        ],
                        'tasks' => [
                            ['id' => 1, 'name' => 'Análise de Documentos'],
                            ['id' => 2, 'name' => 'Elaboração de Petição'],
                            ['id' => 3, 'name' => 'Acompanhamento Processual'],
                            ['id' => 4, 'name' => 'Reunião com Cliente']
                        ],
                        'errors' => []
                    ];
                    
                    echo json_encode($response);
                    break;
                    
                case 'users':
                    $response = [
                        'success' => true,
                        'data' => [
                            ['id' => 165892, 'name' => 'AD INTEGRAÇÃO', 'email' => 'AD@KOETZADVOCACIA.COM.BR'],
                            ['id' => 125310, 'name' => 'ADRIELLY THALITA SANTOS', 'email' => 'ADRIELLYTHALITASANTOS@GMAIL.COM'],
                            ['id' => 117643, 'name' => 'ALLEF DANILO', 'email' => 'ALLEFDANILO.SA@GMAIL.COM']
                        ]
                    ];
                    echo json_encode($response);
                    break;
                    
                case 'tasks':
                    $response = [
                        'success' => true,
                        'data' => [
                            ['id' => 1, 'name' => 'Análise de Documentos'],
                            ['id' => 2, 'name' => 'Elaboração de Petição'],
                            ['id' => 3, 'name' => 'Acompanhamento Processual'],
                            ['id' => 4, 'name' => 'Reunião com Cliente']
                        ]
                    ];
                    echo json_encode($response);
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