<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // tipo do erro
            $table->text('message'); // mensagem do erro
            $table->text('stack_trace')->nullable(); // stack trace completo
            $table->string('file')->nullable(); // arquivo onde ocorreu
            $table->integer('line')->nullable(); // linha do arquivo
            $table->string('url')->nullable(); // URL onde ocorreu
            $table->string('method')->nullable(); // método HTTP
            $table->json('request_data')->nullable(); // dados da requisição
            $table->string('user_agent')->nullable(); // navegador/dispositivo
            $table->string('ip')->nullable(); // IP do usuário
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // usuário logado
            $table->boolean('resolved')->default(false); // se foi resolvido
            $table->text('resolution_notes')->nullable(); // notas sobre a resolução
            $table->timestamp('resolved_at')->nullable(); // quando foi resolvido
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('error_logs');
    }
}; 