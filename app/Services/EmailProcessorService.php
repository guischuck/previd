<?php

namespace App\Services;

use App\Models\Intimacao;
use App\Models\Processo;
use Carbon\Carbon;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmailProcessorService
{
    private $client;
    private $folder;

    public function __construct()
    {
        $this->client = Client::account('gmail');
    }

    public function connect()
    {
        try {
            $this->client->connect();
            $this->folder = $this->client->getFolder('INBOX');
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao conectar com Gmail: ' . $e->getMessage());
            return false;
        }
    }

    public function processEmails()
    {
        if (!$this->connect()) {
            return false;
        }

        try {
            // Busca emails não lidos
            $messages = $this->folder->query()->unseen()->get();

            foreach ($messages as $message) {
                $this->processEmail($message);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar emails: ' . $e->getMessage());
            return false;
        }
    }

    private function processEmail($message)
    {
        try {
            // Extrai o protocolo do assunto ou corpo
            $protocolo = $this->extractProtocolo($message->getSubject(), $message->getTextBody());

            if (!$protocolo) {
                Log::warning('Protocolo não encontrado no email: ' . $message->getSubject());
                return false;
            }

            // Busca o processo pelo protocolo
            $processo = Processo::where('protocolo', $protocolo)->first();

            if (!$processo) {
                Log::warning('Processo não encontrado para o protocolo: ' . $protocolo);
                return false;
            }

            // Processa anexos
            $anexos = $this->processAttachments($message);

            // Cria a intimação
            Intimacao::create([
                'processo_id' => $processo->id,
                'protocolo' => $protocolo,
                'assunto' => $message->getSubject(),
                'conteudo' => $message->getTextBody(),
                'email_origem' => $message->getFrom()[0]->mail,
                'anexos' => $anexos,
                'data_recebimento' => Carbon::parse($message->getDate()),
                'status' => 'processado'
            ]);

            // Marca email como lido
            $message->setFlag('seen');

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar email individual: ' . $e->getMessage());
            return false;
        }
    }

    private function extractProtocolo($subject, $body)
    {
        // Padrão do protocolo INSS (ajuste conforme necessário)
        $pattern = '/\b\d{16}\b/';

        // Procura no assunto
        if (preg_match($pattern, $subject, $matches)) {
            return $matches[0];
        }

        // Procura no corpo
        if (preg_match($pattern, $body, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function processAttachments($message)
    {
        $anexos = [];

        foreach ($message->getAttachments() as $attachment) {
            try {
                $fileName = $attachment->getName();
                $path = 'intimacoes/' . date('Y/m/d') . '/' . uniqid() . '_' . $fileName;

                // Salva o anexo
                Storage::put($path, $attachment->getContent());

                $anexos[] = [
                    'nome' => $fileName,
                    'path' => $path,
                    'mime_type' => $attachment->getMimeType()
                ];
            } catch (\Exception $e) {
                Log::error('Erro ao processar anexo: ' . $e->getMessage());
            }
        }

        return $anexos;
    }
} 