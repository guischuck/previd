<?php

namespace App\Console\Commands;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class ImportLegalDocuments extends Command
{
    protected $signature = 'legal-documents:import';
    protected $description = 'Importa documentos jurídicos da pasta public/conhecimento';

    public function handle()
    {
        $this->info('Iniciando importação de documentos jurídicos...');

        // Verificar se existe um usuário admin
        $admin = User::where('email', 'admin@sistema.com')->first();
        if (!$admin) {
            $this->error('Usuário admin não encontrado!');
            return 1;
        }

        // Pasta de origem
        $sourcePath = public_path('conhecimento');
        if (!File::exists($sourcePath)) {
            $this->error('Pasta conhecimento não encontrada!');
            return 1;
        }

        // Listar arquivos PDF
        $files = File::files($sourcePath);
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            if ($file->getExtension() !== 'pdf') {
                continue;
            }

            $fileName = $file->getFilename();
            $fileSize = $file->getSize();

            // Determinar o tipo baseado no nome do arquivo
            $type = $this->determineDocumentType($fileName);

            // Copiar arquivo para storage
            $newPath = Storage::disk('public')->putFile(
                'legal-documents',
                $file->getPathname()
            );

            // Criar documento
            $document = new LegalDocument([
                'title' => $this->generateTitle($fileName),
                'type' => $type,
                'file_path' => $newPath,
                'file_name' => $fileName,
                'mime_type' => 'application/pdf',
                'file_size' => $fileSize,
                'uploaded_by' => $admin->id
            ]);

            $document->save();

            // Processar PDF em background
            dispatch(function() use ($document) {
                $parser = new Parser();
                $pdf = $parser->parseFile(storage_path('app/public/' . $document->file_path));
                
                $text = $pdf->getText();
                
                $document->update([
                    'extracted_text' => $text,
                    'is_processed' => true
                ]);
            })->afterResponse();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Importação concluída com sucesso!');

        return 0;
    }

    private function determineDocumentType(string $fileName): string
    {
        $fileName = strtolower($fileName);

        if (str_contains($fileName, 'acordao')) {
            return 'acordao';
        }

        if (is_numeric(pathinfo($fileName, PATHINFO_FILENAME))) {
            return 'lei';
        }

        return 'jurisprudencia';
    }

    private function generateTitle(string $fileName): string
    {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Se for um número, assumimos que é uma lei
        if (is_numeric($baseName)) {
            return "Lei {$baseName}";
        }

        // Remover extensão e formatar
        $title = str_replace(['-', '_'], ' ', $baseName);
        $title = ucwords($title);

        return $title;
    }
} 