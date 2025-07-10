<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, converter o enum para string
        Schema::table('cases', function (Blueprint $table) {
            $table->string('status')->default('pendente')->change();
        });

        // Atualizar os status existentes
        DB::statement("UPDATE cases SET status = 'arquivado' WHERE status = 'rejeitado'");

        // Depois, converter para o novo enum
        Schema::table('cases', function (Blueprint $table) {
            $table->enum('status', [
                'pendente', 
                'em_coleta', 
                'aguarda_peticao', 
                'protocolado', 
                'concluido', 
                'arquivado'
            ])->default('pendente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primeiro, converter o enum para string
        Schema::table('cases', function (Blueprint $table) {
            $table->string('status')->default('pendente')->change();
        });

        // Reverter os status
        DB::statement("UPDATE cases SET status = 'rejeitado' WHERE status = 'arquivado'");

        // Depois, converter para o enum anterior
        Schema::table('cases', function (Blueprint $table) {
            $table->enum('status', [
                'pendente', 
                'em_coleta', 
                'aguarda_peticao', 
                'protocolado', 
                'concluido', 
                'rejeitado'
            ])->default('pendente')->change();
        });
    }
}; 