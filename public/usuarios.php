<?php
// Configuração da API do AdvBox
$api_key = 'Cu3xUFd0EA6ZgM8RdqvLT9lYV0c1UGjONTsb2PlBZh1e2mx6pC8JdjhWHVSh';
$base_url = 'https://app.advbox.com.br/api/v1';

// Função para fazer requisições à API
function makeApiRequest($url, $api_key) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false || $http_code !== 200) {
        return null;
    }
    
    return json_decode($response, true);
}

// Processar busca por data específica
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $current_date = $_GET['date'];
} else {
    $current_date = date('Y-m-d');
}

// Obter rewards dos usuários para a data especificada
$rewards = makeApiRequest($base_url . '/users/rewards?date=' . $current_date, $api_key);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários e Rewards - <?php echo date('d/m/Y', strtotime($current_date)); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .date-selector {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .users-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .user-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-item:last-child {
            border-bottom: none;
        }
        .user-item:hover {
            background-color: #f8f9fa;
        }
        .user-name {
            font-weight: 600;
            color: #495057;
        }
        .reward-value {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .reward-zero {
            background: #6c757d;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .total-info {
            background: #e9ecef;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            color: #495057;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .btn-search:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Usuários e Rewards</h1>
            <p>Data: <?php echo date('d/m/Y', strtotime($current_date)); ?></p>
        </div>

        <div class="date-selector">
            <form method="GET">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="date" class="form-label">Selecionar Data:</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $current_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-search w-100">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($rewards && is_array($rewards) && !empty($rewards)): ?>
            <div class="users-list">
                <div class="total-info">
                    Total de usuários: <?php echo count($rewards); ?>
                </div>
                
                <?php foreach ($rewards as $user): ?>
                    <div class="user-item">
                        <div class="user-name">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </div>
                        <div>
                            <?php if ($user['rewards'] === null || $user['rewards'] === '' || $user['rewards'] === '0'): ?>
                                <span class="reward-zero">0</span>
                            <?php else: ?>
                                <span class="reward-value"><?php echo number_format($user['rewards'], 0, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="users-list">
                <div class="no-data">
                    <h4>Nenhum usuário encontrado</h4>
                    <p>Não há dados disponíveis para a data selecionada.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 