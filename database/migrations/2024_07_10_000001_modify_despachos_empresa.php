<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('despachos', function (Blueprint $table) {
            // Remove a foreign key
            $table->dropForeign(['id_empresa']);
            
            // Torna a coluna nullable
            $table->unsignedBigInteger('id_empresa')->nullable()->change();
            
            // Adiciona a foreign key novamente, mas permitindo null
            $table->foreign('id_empresa')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('despachos', function (Blueprint $table) {
            // Remove a foreign key
            $table->dropForeign(['id_empresa']);
            
            // Volta a coluna para not null
            $table->unsignedBigInteger('id_empresa')->nullable(false)->change();
            
            // Adiciona a foreign key original
            $table->foreign('id_empresa')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('cascade');
        });
    }
}; 