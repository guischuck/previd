<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = new mysqli('127.0.0.1', 'previdia_user', '8W1evE36rM6p@=^p', 'previdia');
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
    
    // Verificar estrutura da tabela
    $result = $conn->query("DESCRIBE companies");
    
    if (!$result) {
        throw new Exception("Erro ao verificar estrutura: " . $conn->error);
    }
    
    $colunas = [];
    while ($row = $result->fetch_assoc()) {
        $colunas[] = $row;
    }
    
    // Verificar primeiros registros (sem mostrar dados sensíveis)
    $registros = [];
    $result2 = $conn->query("SELECT id, name, created_at, updated_at FROM companies LIMIT 2");
    if ($result2) {
        while ($row = $result2->fetch_assoc()) {
            $registros[] = $row;
        }
        $result2->close();
    }
    
    echo json_encode([
        'success' => true,
        'estrutura_tabela' => $colunas,
        'exemplo_registros' => $registros
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