<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // acordao, lei, jurisprudencia, etc
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->json('extracted_text')->nullable(); // Texto extraído do PDF para busca
            $table->json('metadata')->nullable(); // Dados adicionais como número da lei, data do acórdão, etc
            $table->boolean('is_processed')->default(false);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            
            // Índices
            $table->index('type');
            $table->index('title');
            $table->fulltext(['title', 'description']); // Para busca textual
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
}; 