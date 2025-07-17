<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('advbox_api_key')->nullable()->after('name');
            $table->boolean('advbox_integration_enabled')->default(false)->after('advbox_api_key');
        });

        // Configurar a empresa ID 2 com a chave da API
        DB::table('companies')
            ->where('id', 2)
            ->update([
                'advbox_api_key' => 'Cu3xUFd0EA6ZgM8RdqvLT9lYV0c1UGjONTsb2PlBZh1e2mx6pC8JdjhWHVSh',
                'advbox_integration_enabled' => true
            ]);
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('advbox_api_key');
            $table->dropColumn('advbox_integration_enabled');
        });
    }
}; 