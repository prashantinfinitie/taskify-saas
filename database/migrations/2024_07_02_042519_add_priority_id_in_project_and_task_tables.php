<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('priority_id')->nullable()->after('status_id');
            $table->longText('note')->nullable()->after('is_favorite');
            $table->string('task_accessibility', 28)->default('assigned_users')->after('is_favorite');

            // Adding foreign key constraint for priority_id
            $table->foreign('priority_id')->references('id')->on('priorities')->onDelete('set null');
        });
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the 'priority' column
            $table->dropColumn('priority');

            // Add 'priority_id' column
            $table->unsignedBigInteger('priority_id')->nullable()->after('status_id');
            $table->foreign('priority_id')->references('id')->on('priorities')->onDelete('set null');
            // Add 'note' column
            $table->longText('note')->nullable()->after('due_date');
        });
        Schema::table('templates', function (Blueprint $table) {
            // Change the 'content' column
            $table->text('content')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable()->default(null)->change();

            // Change the 'status' column
            $table->tinyInteger('status', false, true)->length(4)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['priority_id']);
            // Then drop the columns
            $table->dropColumn('priority_id');
            $table->dropColumn('note');
            $table->dropColumn('task_accessibility');
        });
        Schema::table('tasks', function (Blueprint $table) {
            // Add 'priority' column back
            $table->string('priority')->after('status_id');

            // Drop the 'priority_id' and 'note' columns
            $table->dropColumn('priority_id');
            $table->dropColumn('note');
        });
        Schema::table('templates', function (Blueprint $table) {
            // Revert the 'content' column
            $table->text('content')->change();

            // Revert the 'status' column
            $table->tinyInteger('status')->change();
        });
    }
};
