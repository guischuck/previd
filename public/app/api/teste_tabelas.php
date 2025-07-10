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
    
    // Listar todos os bancos de dados
    $result = $conn->query("SHOW DATABASES");
    $databases = [];
    while ($row = $result->fetch_array()) {
        $databases[] = $row[0];
    }
    
    // Listar todas as tabelas do banco atual
    $result = $conn->query("SHOW TABLES");
    $tabelas = [];
    while ($row = $result->fetch_array()) {
        $tabelas[] = $row[0];
    }
    
    // Procurar por tabelas que podem ser relacionadas a empresas
    $tabelas_possiveis = [];
    foreach ($tabelas as $tabela) {
        if (stripos($tabela, 'company') !== false || 
            stripos($tabela, 'empresa') !== false || 
            stripos($tabela, 'business') !== false || 
            stripos($tabela, 'organization') !== false) {
            $tabelas_possiveis[] = $tabela;
        }
    }
    
    echo json_encode([
        'success' => true,
        'banco_atual' => 'previdia',
        'bancos_disponiveis' => $databases,
        'todas_tabelas' => $tabelas,
        'tabelas_possiveis_empresas' => $tabelas_possiveis
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