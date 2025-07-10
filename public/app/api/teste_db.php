<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    // Tentar conexão
    $conn = new mysqli('127.0.0.1', 'previdia_user', '8W1evE36rM6p@=^p', 'previdia');
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
    
    // Testar consulta na tabela empresas
    $result = $conn->query("SELECT id, razao_social, api_key FROM empresas LIMIT 5");
    
    if (!$result) {
        throw new Exception("Erro na consulta: " . $conn->error);
    }
    
    $empresas = [];
    while ($row = $result->fetch_assoc()) {
        // Mascarar a api_key por segurança
        $row['api_key'] = substr($row['api_key'], 0, 5) . '...';
        $empresas[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'mensagem' => 'Conexão e consulta realizadas com sucesso',
        'total_empresas' => $result->num_rows,
        'primeiras_empresas' => $empresas
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $result->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 