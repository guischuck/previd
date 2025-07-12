<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->foreignId('id_empresa')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->foreignId('id_empresa')->nullable(false)->change();
        });
    }
}; 