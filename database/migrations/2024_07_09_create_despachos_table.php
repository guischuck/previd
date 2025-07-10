<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('companies');
            $table->string('protocolo');
            $table->text('conteudo');
            $table->timestamp('data_email');
            $table->timestamps();

            // Índices
            $table->index('protocolo');
            $table->index(['id_empresa', 'protocolo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('despachos');
    }
}; 