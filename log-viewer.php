<?php
// Painel Web Simples para visualizar os últimos erros do Laravel
// Salve este arquivo na raiz do projeto e acesse via navegador

$logFile = __DIR__ . '/storage/logs/laravel.log';
$linesToShow = 200;

function tailCustom($filepath, $lines = 200) {
    if (!is_readable($filepath)) return [];
    $f = fopen($filepath, 'r');
    $cursor = -1;
    $linesArr = [];
    $buffer = '';
    fseek($f, $cursor, SEEK_END);
    $char = fgetc($f);
    while (count($linesArr) < $lines && ftell($f) > 1) {
        if ($char === "\n") {
            if ($buffer !== '') {
                array_unshift($linesArr, strrev($buffer));
            }
            $buffer = '';
        } else {
            $buffer .= $char;
        }
        fseek($f, --$cursor, SEEK_END);
        $char = fgetc($f);
    }
    if ($buffer !== '') {
        array_unshift($linesArr, strrev($buffer));
    }
    fclose($f);
    return $linesArr;
}

$logLines = tailCustom($logFile, $linesToShow);

function highlight($line) {
    if (strpos($line, 'local.ERROR') !== false) {
        return '<span style="color:#fff;background:#c0392b;padding:2px 4px;border-radius:3px;">' . htmlspecialchars($line) . '</span>';
    }
    if (stripos($line, 'Erro na URL:') !== false) {
        return '<span style="color:#fff;background:#2980b9;padding:2px 4px;border-radius:3px;">' . htmlspecialchars($line) . '</span>';
    }
    return htmlspecialchars($line);
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Log Viewer Laravel</title>
    <style>
        body { font-family: monospace; background: #222; color: #eee; padding: 20px; }
        .log-container { background: #181818; border-radius: 8px; padding: 16px; max-width: 900px; margin: auto; }
        h1 { color: #f1c40f; }
        .log-line { margin-bottom: 2px; white-space: pre-wrap; }
        .ok { color: #2ecc71; }
        .error { color: #e74c3c; }
        .url { color: #3498db; }
        .footer { margin-top: 30px; color: #888; font-size: 13px; text-align: center; }
    </style>
</head>
<body>
    <div class="log-container">
        <h1>Log Viewer Laravel</h1>
        <p>Exibindo as últimas <?= $linesToShow ?> linhas de <code>storage/logs/laravel.log</code></p>
        <hr>
        <?php if (empty($logLines)): ?>
            <div class="error">Arquivo de log não encontrado ou vazio.</div>
        <?php else: ?>
            <?php foreach ($logLines as $line): ?>
                <div class="log-line"><?= highlight($line) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="footer">Atualize a página para ver novos logs. | Painel simples por IA</div>
    </div>
</body>
</html> 