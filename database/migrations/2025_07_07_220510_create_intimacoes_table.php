<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intimacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->string('protocolo');
            $table->string('assunto');
            $table->text('conteudo');
            $table->string('email_origem');
            $table->json('anexos')->nullable();
            $table->timestamp('data_recebimento');
            $table->enum('status', ['novo', 'processado', 'erro'])->default('novo');
            $table->text('erro_mensagem')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('protocolo');
            $table->index('status');
            $table->index('data_recebimento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intimacoes');
    }
};
