<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Criar tabela de templates de petição se não existir
        if (!Schema::hasTable('petition_templates')) {
            Schema::create('petition_templates', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->string('category');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_global')->default(false);
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Criar tabela de templates de workflow se não existir
        if (!Schema::hasTable('workflow_templates')) {
            Schema::create('workflow_templates', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('tasks');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_global')->default(false);
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Criar tabela de pagamentos se não existir
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('status');
                $table->string('payment_method');
                $table->string('transaction_id')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        // Criar tabela de assinaturas se não existir
        if (!Schema::hasTable('company_subscriptions')) {
            Schema::create('company_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('plan');
                $table->string('status');
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('current_period_start')->nullable();
                $table->timestamp('current_period_end')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('workflow_templates');
        Schema::dropIfExists('petition_templates');
    }
}; 