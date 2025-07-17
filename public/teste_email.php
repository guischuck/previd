<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;

// Configurações IMAP
$hostname = "imap.kinghost.net";
$username = "intimacoes@previdia.com";
$password = "Nova365@";
$port = 143;

function decodificarAssunto($subject) {
    $elements = imap_mime_header_decode($subject);
    $subject = '';
    foreach ($elements as $element) {
        $charset = $element->charset == 'default' ? 'UTF-8' : $element->charset;
        $subject .= mb_convert_encoding($element->text, 'UTF-8', $charset);
    }
    return $subject;
}

function limparConteudo($body) {
    // Decodifica quoted-printable primeiro
    $body = quoted_printable_decode($body);
    
    // Remove caracteres de controle
    $body = preg_replace('/[\x00-\x1F\x7F]/', '', $body);
    
    // Remove caracteres =09 (tab em quoted-printable)
    $body = preg_replace('/=09/', ' ', $body);
    
    // Converte para UTF-8 se necessário
    if (!mb_check_encoding($body, 'UTF-8')) {
        $body = mb_convert_encoding($body, 'UTF-8', 'ISO-8859-1');
    }
    
    // Remove múltiplos espaços
    $body = preg_replace('/\s+/', ' ', $body);
    
    // Remove linhas vazias
    $body = preg_replace('/\n\s*\n/', "\n", $body);
    
    // Decodifica entidades HTML
    $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    return trim($body);
}

echo "=== Teste de Conexão IMAP ===\n\n";
echo "Configurações:\n";
echo "- Host: " . $hostname . "\n";
echo "- Porta: " . $port . "\n";
echo "- Usuário: " . $username . "\n\n";

$mailbox = "{" . $hostname . ":" . $port . "/notls}INBOX";
echo "String de conexão: " . $mailbox . "\n\n";

try {
    // Configurar opções de conexão
    $opts = [
        'DISABLE_AUTHENTICATOR' => ['GSSAPI', 'NTLM', 'PLAIN'],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ];

    echo "Tentando conectar...\n";
    $inbox = imap_open($mailbox, $username, $password, 0, 1, $opts);
    
    if (!$inbox) {
        throw new \Exception("Falha na conexão IMAP: " . imap_last_error());
    }
    
    echo "✓ Conexão estabelecida com sucesso!\n\n";

    // Listar pastas disponíveis
    echo "=== Pastas Disponíveis ===\n";
    $folders = imap_list($inbox, "{" . $hostname . ":" . $port . "}", "*");
    if ($folders) {
        foreach ($folders as $folder) {
            echo "- " . str_replace("{" . $hostname . ":" . $port . "}", "", $folder) . "\n";
        }
    } else {
        echo "Nenhuma pasta encontrada.\n";
    }
    echo "\n";

    // Buscar emails
    echo "=== Buscando Emails ===\n";
    echo "Procurando emails não lidos...\n";
    $emails = imap_search($inbox, 'UNSEEN');
    
    if ($emails === false) {
        echo "✓ Nenhum email não lido encontrado.\n\n";
        
        // Tentar buscar emails recentes
        echo "Buscando 5 emails mais recentes...\n";
        $emails = imap_search($inbox, 'ALL');
        if ($emails) {
            $emails = array_slice($emails, -5);
        }
    }
    
    if ($emails) {
        $total = count($emails);
        echo "Encontrado(s) {$total} email(s)\n\n";
        
        foreach ($emails as $email_number) {
            $header = imap_headerinfo($inbox, $email_number);
            $overview = imap_fetch_overview($inbox, $email_number, 0);
            $structure = imap_fetchstructure($inbox, $email_number);
            
            echo "=== Email #{$email_number} ===\n";
            echo "De: " . ($header->fromaddress ?? 'N/A') . "\n";
            echo "Para: " . ($header->toaddress ?? 'N/A') . "\n";
            echo "Assunto: " . decodificarAssunto($header->subject ?? 'Sem assunto') . "\n";
            echo "Data: " . Carbon::createFromTimestamp(strtotime($header->date))->format('d/m/Y H:i') . "\n";
            echo "Status: " . ($overview[0]->seen ? 'Lido' : 'Não lido') . "\n";
            echo "Message-ID: " . ($header->message_id ?? 'N/A') . "\n";
            
            // Verificar anexos
            if (isset($structure->parts)) {
                $anexos = [];
                foreach ($structure->parts as $part) {
                    if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
                        $anexos[] = strtolower($part->dparameters[0]->value ?? 'anexo-sem-nome');
                    }
                }
                if (!empty($anexos)) {
                    echo "Anexos: " . implode(", ", $anexos) . "\n";
                }
            }
            
            // Buscar corpo do email
            $body = imap_fetchbody($inbox, $email_number, 1);
            
            // Tentar decodificar o corpo
            if ($structure->encoding == 3) { // BASE64
                $body = base64_decode($body);
            }
            
            // Limpar HTML e mostrar conteúdo
            $body = strip_tags($body);
            $body = limparConteudo($body);
            
            echo "\nConteúdo do Email:\n";
            echo "----------------------------------------\n";
            echo $body . "\n";
            echo "----------------------------------------\n\n";

            // Extrair informações específicas do INSS
            if (strpos(strtolower($header->fromaddress), '@inss.gov.br') !== false) {
                echo "=== Informações do INSS ===\n";
                
                // Extrair protocolo
                if (preg_match('/Protocolo:?\s*(\d+)/i', $body, $matches)) {
                    echo "Protocolo: " . $matches[1] . "\n";
                }
                
                // Extrair número do requerimento
                if (preg_match('/requerimento\s+(\d+)/i', $body, $matches)) {
                    echo "Requerimento: " . $matches[1] . "\n";
                }
                
                // Extrair status
                if (preg_match('/status.*?(concluída|deferido|indeferido|em análise)/i', $body, $matches)) {
                    echo "Status: " . $matches[1] . "\n";
                }
                
                // Extrair serviço
                if (preg_match('/Serviço:?\s*([^\n]+)/ui', $body, $matches)) {
                    echo "Serviço: " . trim($matches[1]) . "\n";
                }
                
                // Extrair despacho
                if (preg_match('/Despacho:?\s*([^\n]+(?:\n(?!Protocolo:|Serviço:|Status:)[^\n]+)*)/i', $body, $matches)) {
                    echo "\nDespacho:\n" . trim($matches[1]) . "\n";
                }
                
                echo "----------------------------------------\n\n";
            }
        }
    } else {
        echo "Nenhum email encontrado.\n";
    }
    
    imap_close($inbox);
    echo "\n✓ Teste concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Último erro IMAP: " . imap_last_error() . "\n\n";
    
    // Mostrar erros detalhados
    echo "=== Detalhes do Erro ===\n";
    $error = error_get_last();
    if ($error) {
        echo "Tipo: " . $error['type'] . "\n";
        echo "Mensagem: " . $error['message'] . "\n";
        echo "Arquivo: " . $error['file'] . "\n";
        echo "Linha: " . $error['line'] . "\n";
    }
} 