<?php
// Teste da nova API do AdvBox em PHP puro
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste da Nova API AdvBox</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        button { padding: 10px 15px; margin: 5px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        input { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Teste da Nova API AdvBox</h1>
    <p>Este teste verifica se a nova API em PHP puro está funcionando corretamente.</p>

    <div class="test-section info">
        <h3>1. Teste de Configuração</h3>
        <button onclick="testConfig()">Testar Configuração</button>
        <div id="config-result"></div>
    </div>

    <div class="test-section info">
        <h3>2. Teste de Usuários</h3>
        <button onclick="testUsers()">Buscar Usuários</button>
        <div id="users-result"></div>
    </div>

    <div class="test-section info">
        <h3>3. Teste de Tarefas</h3>
        <button onclick="testTasks()">Buscar Tarefas</button>
        <div id="tasks-result"></div>
    </div>

    <div class="test-section info">
        <h3>4. Teste de Busca de Processo</h3>
        <input type="text" id="protocol-input" placeholder="Digite o número do protocolo" value="123456789">
        <button onclick="testLawsuit()">Buscar Processo</button>
        <div id="lawsuit-result"></div>
    </div>

    <div class="test-section info">
        <h3>5. Teste de Criação de Tarefa</h3>
        <button onclick="testCreateTask()">Criar Tarefa de Teste</button>
        <div id="create-task-result"></div>
    </div>

    <script>
        async function makeRequest(url, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    }
                };
                
                if (data) {
                    options.body = JSON.stringify(data);
                }
                
                const response = await fetch(url, options);
                const result = await response.json();
                
                return {
                    success: response.ok,
                    data: result,
                    status: response.status
                };
            } catch (error) {
                return {
                    success: false,
                    error: error.message
                };
            }
        }

        function displayResult(elementId, result, title) {
            const element = document.getElementById(elementId);
            const className = result.success ? 'success' : 'error';
            
            element.innerHTML = `
                <div class="${className}">
                    <h4>${title}</h4>
                    <p><strong>Status:</strong> ${result.success ? 'Sucesso' : 'Erro'}</p>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                </div>
            `;
        }

        async function testConfig() {
            const result = await makeRequest('/advbox_api.php?endpoint=settings');
            displayResult('config-result', result, 'Configuração da API');
        }

        async function testUsers() {
            const result = await makeRequest('/advbox_api.php?endpoint=users');
            displayResult('users-result', result, 'Usuários');
        }

        async function testTasks() {
            const result = await makeRequest('/advbox_api.php?endpoint=tasks');
            displayResult('tasks-result', result, 'Tarefas');
        }

        async function testLawsuit() {
            const protocol = document.getElementById('protocol-input').value;
            if (!protocol) {
                alert('Digite um número de protocolo');
                return;
            }
            
            const result = await makeRequest(`/advbox_api.php?endpoint=lawsuits&protocol_number=${encodeURIComponent(protocol)}`);
            displayResult('lawsuit-result', result, 'Processo');
        }

        async function testCreateTask() {
            // Dados de teste para criar uma tarefa
            const testData = {
                from: "1",
                guests: ["1"],
                tasks_id: "1",
                lawsuits_id: "1",
                comments: "Tarefa de teste criada via nova API",
                start_date: new Date().toLocaleDateString('pt-BR'),
                start_time: "09:00",
                end_date: new Date().toLocaleDateString('pt-BR'),
                end_time: "17:00",
                date_deadline: new Date().toLocaleDateString('pt-BR'),
                local: "",
                urgent: false,
                important: false,
                display_schedule: true
            };
            
            const result = await makeRequest('/advbox_api.php?endpoint=posts', 'POST', testData);
            displayResult('create-task-result', result, 'Criação de Tarefa');
        }
    </script>
</body>
</html> 