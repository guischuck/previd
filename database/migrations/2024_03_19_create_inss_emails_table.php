<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inss_emails', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo')->unique();
            $table->text('conteudo');
            $table->string('assunto');
            $table->dateTime('data_recebimento');
            $table->boolean('processado')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inss_emails');
    }
}; 