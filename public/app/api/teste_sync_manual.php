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
    
    // Dados de exemplo para teste
    $dados_teste = [
        'id_empresa' => 1, // Ajuste para um ID válido da sua empresa
        'processos' => [
            [
                'protocolo' => 'TESTE123',
                'servico' => 'Serviço de Teste',
                'situacao' => 'Em Análise',
                'ultima_atualizacao' => date('Y-m-d H:i:s'),
                'protocolado_em' => date('Y-m-d H:i:s'),
                'cpf' => '12345678900',
                'nome' => 'Usuário de Teste'
            ]
        ]
    ];
    
    // Verificar empresa
    $stmt = $conn->prepare("SELECT id FROM companies WHERE id = ?");
    $stmt->bind_param('i', $dados_teste['id_empresa']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Empresa de teste não encontrada. Use um ID válido.");
    }
    $stmt->close();
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Inserir processo
        $stmt = $conn->prepare("
            INSERT INTO processos (
                protocolo, servico, situacao, ultima_atualizacao, 
                protocolado_em, cpf, nome, id_empresa, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                servico = VALUES(servico),
                situacao_anterior = CASE 
                    WHEN situacao != VALUES(situacao) THEN situacao 
                    ELSE situacao_anterior 
                END,
                situacao = VALUES(situacao),
                ultima_atualizacao = VALUES(ultima_atualizacao),
                protocolado_em = COALESCE(protocolado_em, VALUES(protocolado_em)),
                nome = VALUES(nome),
                updated_at = NOW()
        ");
        
        foreach ($dados_teste['processos'] as $processo) {
            $stmt->bind_param(
                'sssssssi',
                $processo['protocolo'],
                $processo['servico'],
                $processo['situacao'],
                $processo['ultima_atualizacao'],
                $processo['protocolado_em'],
                $processo['cpf'],
                $processo['nome'],
                $dados_teste['id_empresa']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir processo: " . $stmt->error);
            }
            
            // Se foi uma atualização (não inserção), verificar se precisa registrar mudança de situação
            if ($stmt->affected_rows == 2) { // 2 indica UPDATE
                $id_processo = $conn->insert_id;
                
                // Inserir no histórico
                $stmt_hist = $conn->prepare("
                    INSERT INTO historico_situacoes (
                        id_processo, situacao_anterior, situacao_atual, 
                        id_empresa, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt_hist->bind_param(
                    'issi',
                    $id_processo,
                    $processo['situacao_anterior'],
                    $processo['situacao'],
                    $dados_teste['id_empresa']
                );
                
                if (!$stmt_hist->execute()) {
                    throw new Exception("Erro ao inserir histórico: " . $stmt_hist->error);
                }
                
                $stmt_hist->close();
            }
        }
        
        $stmt->close();
        
        // Commit da transação
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Teste de sincronização realizado com sucesso',
            'dados_testados' => $dados_teste
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        throw $e;
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