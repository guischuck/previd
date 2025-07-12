<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: X-API-Key, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Pegar API Key
$apiKey = null;
$headers = getallheaders();

if (isset($headers['X-API-Key'])) {
    $apiKey = trim($headers['X-API-Key']);
} elseif (isset($_SERVER['HTTP_X_API_KEY'])) {
    $apiKey = trim($_SERVER['HTTP_X_API_KEY']);
} elseif (isset($_GET['api_key'])) {
    $apiKey = trim($_GET['api_key']);
}

if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'API Key não fornecida']);
    exit;
}

// Remover vírgulas se existirem
if (strpos($apiKey, ',') !== false) {
    $parts = explode(',', $apiKey);
    $apiKey = trim($parts[0]);
}

try {
    // Conectar ao banco
    $conn = new mysqli('127.0.0.1', 'previdia_user', '8W1evE36rM6p@=^p', 'previdia');
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // Consultar empresa
    $stmt = $conn->prepare("SELECT id, name as razao_social FROM companies WHERE api_key = ?");
    if (!$stmt) {
        throw new Exception("Erro na consulta: " . $conn->error);
    }

    $stmt->bind_param('s', $apiKey);
    if (!$stmt->execute()) {
        throw new Exception("Erro na execução: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['error' => 'API Key inválida']);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Retornar resultado
    $empresa = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'id_empresa' => (int)$empresa['id'],
        'razao_social' => $empresa['razao_social']
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Erro em get_idempresa.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>