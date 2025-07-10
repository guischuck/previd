<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    $id_empresa = 2; // ID fixo da empresa que queremos limpar
    
    $conn = new mysqli('127.0.0.1', 'previdia_user', '8W1evE36rM6p@=^p', 'previdia');
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
    
    // Verificar se a empresa existe
    $stmt = $conn->prepare("SELECT id, name FROM companies WHERE id = ?");
    $stmt->bind_param('i', $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Empresa não encontrada");
    }
    
    $empresa = $result->fetch_assoc();
    $stmt->close();
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Contar registros antes da exclusão
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM processos WHERE id_empresa = ?");
        $stmt->bind_param('i', $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_processos = $result->fetch_assoc()['total'];
        $stmt->close();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM historico_situacoes WHERE id_empresa = ?");
        $stmt->bind_param('i', $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_historico = $result->fetch_assoc()['total'];
        $stmt->close();
        
        // Excluir histórico primeiro (devido à chave estrangeira)
        $stmt = $conn->prepare("DELETE FROM historico_situacoes WHERE id_empresa = ?");
        $stmt->bind_param('i', $id_empresa);
        $stmt->execute();
        $historico_deletados = $stmt->affected_rows;
        $stmt->close();
        
        // Excluir processos
        $stmt = $conn->prepare("DELETE FROM processos WHERE id_empresa = ?");
        $stmt->bind_param('i', $id_empresa);
        $stmt->execute();
        $processos_deletados = $stmt->affected_rows;
        $stmt->close();
        
        // Commit da transação
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Dados excluídos com sucesso',
            'empresa' => [
                'id' => $empresa['id'],
                'nome' => $empresa['name']
            ],
            'estatisticas' => [
                'processos' => [
                    'total_antes' => $total_processos,
                    'excluidos' => $processos_deletados
                ],
                'historico' => [
                    'total_antes' => $total_historico,
                    'excluidos' => $historico_deletados
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw new Exception("Erro ao excluir dados: " . $e->getMessage());
    }
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 