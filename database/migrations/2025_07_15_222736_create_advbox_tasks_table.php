<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advbox_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advbox_id')->nullable()->comment('ID da tarefa no AdvBox');
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('Usuário que criou a tarefa');
            $table->string('from')->nullable()->comment('ID do remetente no AdvBox');
            $table->json('guests')->nullable()->comment('IDs dos convidados no AdvBox');
            $table->string('tasks_id')->nullable()->comment('ID da tarefa relacionada no AdvBox');
            $table->string('lawsuits_id')->nullable()->comment('ID do processo no AdvBox');
            $table->text('comments')->nullable()->comment('Comentários da tarefa');
            $table->string('start_date')->nullable()->comment('Data de início (formato DD/MM/AAAA)');
            $table->string('start_time')->nullable()->comment('Hora de início (formato HH:MM)');
            $table->string('end_date')->nullable()->comment('Data de término (formato DD/MM/AAAA)');
            $table->string('end_time')->nullable()->comment('Hora de término (formato HH:MM)');
            $table->string('date_deadline')->nullable()->comment('Data limite (formato DD/MM/AAAA)');
            $table->string('local')->nullable()->comment('Local da tarefa');
            $table->boolean('urgent')->default(false)->comment('Indica se a tarefa é urgente');
            $table->boolean('important')->default(false)->comment('Indica se a tarefa é importante');
            $table->boolean('display_schedule')->default(true)->comment('Indica se a tarefa deve ser exibida na agenda');
            $table->string('date')->nullable()->comment('Data geral (formato DD/MM/AAAA)');
            $table->string('folder')->nullable()->comment('Pasta relacionada');
            $table->string('protocol_number')->nullable()->comment('Número de protocolo');
            $table->string('process_number')->nullable()->comment('Número do processo');
            $table->json('api_response')->nullable()->comment('Resposta completa da API em formato JSON');
            $table->enum('status', ['pending', 'sent', 'error'])->default('pending')->comment('Status da sincronização');
            $table->text('error_message')->nullable()->comment('Mensagem de erro, se houver');
            $table->timestamps();
            
            // Índices para melhorar a performance das consultas
            $table->index('advbox_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advbox_tasks');
    }
};
