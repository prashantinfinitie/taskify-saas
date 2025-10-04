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
        Schema::create('task_time_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->text('user_id');
            $table->unsignedBigInteger('workspace_id');

            $table->date('entry_date');
            $table->boolean('is_billable')->default(false);
            $table->enum('entry_type', ['standard', 'flexible']);
            $table->time('standard_hours')->nullable(); // For standard type
            $table->time('start_time')->nullable(); // For flexible type
            $table->time('end_time')->nullable(); // For flexible type
            $table->longText('description')->nullable();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_time_entries');
    }
};
