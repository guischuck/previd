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
            $table->string('protocolo');
            $table->string('servico')->nullable();
            $table->text('conteudo');
            $table->dateTime('data_email');
            $table->string('email_id')->unique(); // ID único do email para evitar duplicatas
            $table->unsignedBigInteger('id_empresa');
            $table->timestamps();
            
            $table->foreign('id_empresa')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
                
            $table->index('protocolo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despachos');
    }
}; 