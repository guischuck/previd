<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLawsuitRelatedTables extends Migration
{
    public function up()
    {
        // Tabela de processos judiciais
        Schema::create('lawsuits', function (Blueprint $table) {
            $table->id();
            $table->string('process_number')->nullable()->unique();
            $table->string('protocol_number')->nullable()->unique();
            $table->enum('status', ['em_andamento', 'concluido', 'suspenso'])->default('em_andamento');
            $table->timestamps();
        });

        // Tabela de relacionamento entre processos e clientes
        Schema::create('lawsuit_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lawsuit_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamps();

            $table->foreign('lawsuit_id')->references('id')->on('lawsuits')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        // Tabela de movimentações de processos
        Schema::create('lawsuit_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lawsuit_id');
            $table->date('date');
            $table->text('description');
            $table->enum('type', ['judicial', 'administrativa', 'recursal'])->default('judicial');
            $table->timestamps();

            $table->foreign('lawsuit_id')->references('id')->on('lawsuits')->onDelete('cascade');
        });

        // Tabela de relacionamento entre processos e tarefas
        Schema::create('lawsuit_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lawsuit_id');
            $table->unsignedBigInteger('task_id');
            $table->timestamps();

            $table->foreign('lawsuit_id')->references('id')->on('lawsuits')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lawsuit_tasks');
        Schema::dropIfExists('lawsuit_movements');
        Schema::dropIfExists('lawsuit_customers');
        Schema::dropIfExists('lawsuits');
    }
} 