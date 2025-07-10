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
    
    $tabelas = [
        'processos',
        'historico_situacoes',
        'companies'
    ];
    
    $estruturas = [];
    $status = [];
    
    foreach ($tabelas as $tabela) {
        // Verificar se a tabela existe
        $result = $conn->query("SHOW TABLES LIKE '$tabela'");
        $existe = $result->num_rows > 0;
        
        if ($existe) {
            // Pegar estrutura da tabela
            $result = $conn->query("DESCRIBE $tabela");
            $colunas = [];
            while ($row = $result->fetch_assoc()) {
                $colunas[] = $row;
            }
            $estruturas[$tabela] = $colunas;
        }
        
        $status[$tabela] = [
            'existe' => $existe,
            'colunas_necessarias' => [
                'processos' => ['id', 'protocolo', 'servico', 'situacao', 'situacao_anterior', 'ultima_atualizacao', 'protocolado_em', 'cpf', 'nome', 'id_empresa'],
                'historico_situacoes' => ['id', 'id_processo', 'situacao_anterior', 'situacao_atual', 'data_mudanca', 'id_empresa'],
                'companies' => ['id', 'name', 'api_key']
            ][$tabela] ?? [],
            'colunas_encontradas' => $existe ? array_column($estruturas[$tabela], 'Field') : []
        ];
        
        if ($existe) {
            $status[$tabela]['colunas_faltantes'] = array_diff(
                $status[$tabela]['colunas_necessarias'],
                $status[$tabela]['colunas_encontradas']
            );
        }
    }
    
    echo json_encode([
        'success' => true,
        'status_tabelas' => $status,
        'estruturas_detalhadas' => $estruturas
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 