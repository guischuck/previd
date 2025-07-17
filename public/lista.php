<?php
require __DIR__.'/../vendor/autoload.php';

// Carrega o arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurações do banco de dados do .env
$host = $_ENV['DB_HOST'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    // Conexão com o banco
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Busca os despachos
    $query = $pdo->query("
        SELECT 
            d.protocolo,
            d.servico,
            d.conteudo,
            d.data_email,
            d.created_at,
            c.name as empresa
        FROM despachos d
        LEFT JOIN companies c ON c.id = d.id_empresa
        ORDER BY d.created_at DESC
    ");
    
    $despachos = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Despachos INSS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Despachos Recebidos do INSS</h1>
        
        <?php if (empty($despachos)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                Nenhum despacho encontrado ainda.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Protocolo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Serviço
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Empresa
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data do Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Despacho
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($despachos as $despacho): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-mono">
                                    <?php echo htmlspecialchars($despacho['protocolo']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo htmlspecialchars($despacho['servico']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo htmlspecialchars($despacho['empresa']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo date('d/m/Y H:i', strtotime($despacho['data_email'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xl break-words">
                                        <?php echo nl2br(htmlspecialchars($despacho['conteudo'])); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-sm text-gray-500">
            <p>Última atualização: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <script>
        // Recarrega a página a cada 5 minutos
        setTimeout(() => {
            window.location.reload();
        }, 5 * 60 * 1000);
    </script>
</body>
</html> 