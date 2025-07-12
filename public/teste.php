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

} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emails INSS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Emails Processados do INSS</h1>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Protocolo</th>
                        <th>Assunto</th>
                        <th>Data Recebimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emails as $email): ?>
                    <tr>
                        <td><?= htmlspecialchars($email['protocolo']) ?></td>
                        <td><?= htmlspecialchars($email['assunto']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($email['data_recebimento'])) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#emailModal<?= $email['id'] ?>">
                                Ver Conteúdo
                            </button>
                        </td>
                    </tr>

                    <!-- Modal para exibir o conteúdo do email -->
                    <div class="modal fade" id="emailModal<?= $email['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalhes do Email</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <h6>Protocolo: <?= htmlspecialchars($email['protocolo']) ?></h6>
                                    <h6>Assunto: <?= htmlspecialchars($email['assunto']) ?></h6>
                                    <h6>Data: <?= date('d/m/Y H:i', strtotime($email['data_recebimento'])) ?></h6>
                                    <hr>
                                    <div class="content-wrapper">
                                        <?= nl2br(htmlspecialchars($email['conteudo'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>