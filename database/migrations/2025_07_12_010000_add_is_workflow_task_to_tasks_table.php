<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_workflow_task')->default(false)->after('notes');
            $table->integer('order')->default(0)->after('is_workflow_task');
            $table->foreignId('workflow_template_id')->nullable()->constrained()->onDelete('set null')->after('case_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['workflow_template_id']);
            $table->dropColumn(['is_workflow_task', 'order', 'workflow_template_id']);
        });
    }
};
