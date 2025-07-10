<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nova mensagem de suporte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .field-value {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .message-content {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Nova mensagem de suporte</h2>
        <p>Uma nova mensagem foi enviada através do formulário de suporte do sistema.</p>
    </div>

    <div class="content">
        <div class="field">
            <div class="field-label">Nome:</div>
            <div class="field-value">{{ $name }}</div>
        </div>

        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">{{ $email }}</div>
        </div>

        <div class="field">
            <div class="field-label">Assunto:</div>
            <div class="field-value">{{ $subject }}</div>
        </div>

        <div class="field">
            <div class="field-label">Mensagem:</div>
            <div class="message-content">{{ $message }}</div>
        </div>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; text-align: center; color: #6c757d; font-size: 14px;">
        <p>Esta mensagem foi enviada automaticamente pelo sistema de suporte da Previdia.</p>
        <p>Data e hora: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html> 