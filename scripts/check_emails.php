<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurações do banco de dados
$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['DB_DATABASE'] ?? 'laravel';
$dbUser = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';

try {
    // Conectar ao banco de dados
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Buscar emails processados
    $stmt = $pdo->query("SELECT * FROM inss_emails ORDER BY data_recebimento DESC");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Exibir resultados
    foreach ($emails as $email) {
        echo "==========================================================\n";
        echo "Protocolo: " . $email['protocolo'] . "\n";
        echo "Assunto: " . $email['assunto'] . "\n";
        echo "Data: " . $email['data_recebimento'] . "\n";
        echo "Conteúdo:\n" . $email['conteudo'] . "\n\n";
    }

} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
} 