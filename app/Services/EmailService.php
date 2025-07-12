<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Despacho;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmailService
{
    private $mailbox;
    private $company;

    public function __construct()
    {
        // Busca a primeira empresa ativa por padrão
        $this->company = \App\Models\Company::active()->first();

        if (!$this->company) {
            throw new \Exception('Nenhuma empresa ativa encontrada');
        }

        Log::info('EmailService inicializado', [
            'host' => config('imap.host'),
            'username' => config('imap.username'),
            'company_id' => $this->company->id
        ]);

        $this->connect();
    }

    private function connect()
    {
        $host = config('imap.host');
        $port = config('imap.port');
        $username = config('imap.username');
        $password = config('imap.password');

        // Configuração específica para KingHost
        $mailbox = "{" . $host . ":" . $port . "/imap/notls}INBOX";

        Log::info('Tentando conectar ao IMAP', [
            'mailbox' => $mailbox,
            'host' => $host,
            'port' => $port,
            'username' => $username
        ]);

        $opts = [
            'DISABLE_AUTHENTICATOR' => 'GSSAPI NTLM PLAIN',
            'DISABLE_AUTHENTICATOR' => 'PLAIN'
        ];

        $this->mailbox = imap_open($mailbox, $username, $password, 0, 1, $opts);

        if (!$this->mailbox) {
            $error = imap_last_error();
            Log::error('Erro ao conectar ao IMAP', [
                'error' => $error
            ]);
            throw new \Exception('Erro ao conectar ao servidor IMAP: ' . $error);
        }

        Log::info('Conexão IMAP estabelecida com sucesso');
    }

    public function processEmails()
    {
        try {
            Log::info('Buscando emails não lidos');

            // Busca emails não lidos
            $emails = imap_search($this->mailbox, 'UNSEEN');

            Log::info('Emails não lidos encontrados', [
                'count' => $emails ? count($emails) : 0,
                'emails' => $emails
            ]);

            if (!$emails) {
                Log::info('Nenhum email novo encontrado');
                return true;
            }

            // Filtra apenas emails do INSS
            $emailsInss = array_filter($emails, function($emailId) {
                $header = imap_headerinfo($this->mailbox, $emailId);
                $isInss = stripos($header->fromaddress, '@inss.gov.br') !== false;
                
                Log::info('Verificando email', [
                    'email_id' => $emailId,
                    'from' => $header->fromaddress,
                    'is_inss' => $isInss
                ]);
                
                return $isInss;
            });

            Log::info('Emails não lidos do INSS encontrados', [
                'count' => count($emailsInss),
                'emails' => $emailsInss
            ]);

            if (empty($emailsInss)) {
                Log::info('Nenhum email novo do INSS encontrado');
                return true;
            }

            // Processa cada email do INSS
            foreach ($emailsInss as $emailId) {
                $this->processEmail($emailId);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Erro ao processar emails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function decodeEmailText($text)
    {
        // Decodifica quoted-printable
        $decodedText = quoted_printable_decode($text);
        
        // Remove espaços extras e quebras de linha desnecessárias
        $cleanedText = preg_replace('/\s+/', ' ', trim($decodedText));

        return $cleanedText;
    }

    private function formatDespacho($text)
    {
        // Remove espaços extras e quebras de linha
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Remove caracteres de controle
        $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);

        // Decodifica HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }

    private function processEmail($emailId)
    {
        try {
            $header = imap_headerinfo($this->mailbox, $emailId);
            $body = imap_fetchbody($this->mailbox, $emailId, 1);
            
            // Extrai o message_id do cabeçalho
            $messageId = $header->message_id ?? null;
            if (empty($messageId)) {
                $messageId = md5($header->date . $header->subject . $emailId);
            }
            
            Log::info('Processando email', [
                'email_id' => $emailId,
                'message_id' => $messageId,
                'from' => $header->fromaddress,
                'subject' => $header->subject,
                'date' => $header->date,
                'company_id' => $this->company->id
            ]);
            
            // Decodifica e limpa o corpo do email
            $body = $this->decodeEmailText($body);

            Log::info('Corpo do email após decodificação', [
                'body' => $body
            ]);

            // Extrai o protocolo
            preg_match('/<b>Protocolo<\/b>:\s*(\d+)/i', $body, $protocoloMatches);
            $protocolo = $protocoloMatches[1] ?? null;

            // Extrai o serviço
            preg_match('/<b>Servi.o<\/b>:\s*([^<]+)/ui', $body, $servicoMatches);
            $servico = isset($servicoMatches[1]) ? trim($servicoMatches[1]) : null;

            // Extrai o despacho
            preg_match('/<b>Despacho:<\/b><\/div>\s*<div[^>]*>(.+?)<\/div>/si', $body, $despachoMatches);
            $conteudoDespacho = null;
            if (!empty($despachoMatches[1])) {
                // Remove tags HTML, decodifica entidades e limpa espaços
                $conteudoDespacho = trim(html_entity_decode(strip_tags($despachoMatches[1]), ENT_QUOTES, 'UTF-8'));
            }

            Log::info('Dados extraídos do email', [
                'protocolo' => $protocolo,
                'servico' => $servico,
                'despacho' => $conteudoDespacho,
                'body_length' => strlen($body)
            ]);

            if ($protocolo && $conteudoDespacho) {
                try {
                    // Verifica se já existe um despacho com este email_id
                    $despachoExistente = \App\Models\Despacho::where('email_id', $messageId)->first();
                    
                    if (!$despachoExistente) {
                        Log::info('Tentando salvar despacho', [
                            'email_id' => $messageId,
                            'id_empresa' => $this->company->id,
                            'protocolo' => $protocolo,
                            'servico' => $servico,
                            'data_email' => Carbon::createFromTimestamp(strtotime($header->date))->format('Y-m-d H:i:s')
                        ]);

                        // Cria o despacho no banco
                        $despacho = new \App\Models\Despacho();
                        $despacho->email_id = $messageId;
                        $despacho->id_empresa = $this->company->id;
                        $despacho->protocolo = $protocolo;
                        $despacho->servico = $servico;
                        $despacho->conteudo = $conteudoDespacho;
                        $despacho->data_email = Carbon::createFromTimestamp(strtotime($header->date));
                        $despacho->save();

                        Log::info('Despacho salvo com sucesso', [
                            'despacho_id' => $despacho->id,
                            'protocolo' => $protocolo,
                            'message_id' => $messageId
                        ]);
                    } else {
                        Log::info('Despacho já existe no banco', [
                            'email_id' => $messageId,
                            'despacho_id' => $despachoExistente->id
                        ]);
                    }

                    // Marca o email como lido
                    imap_setflag_full($this->mailbox, $emailId, "\\Seen");
                    Log::info('Email marcado como lido', ['email_id' => $emailId]);
                } catch (\Exception $e) {
                    Log::error('Erro ao salvar despacho no banco', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'protocolo' => $protocolo,
                        'message_id' => $messageId,
                        'sql_error' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
                    ]);
                }
            } else {
                Log::warning('Email não contém protocolo ou despacho', [
                    'protocolo_encontrado' => $protocolo,
                    'despacho_encontrado' => $conteudoDespacho,
                    'message_id' => $messageId
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao processar email individual', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'last_error' => imap_last_error()
            ]);
        }
    }

    public function __destruct()
    {
        if ($this->mailbox) {
            imap_close($this->mailbox);
        }
    }
} 