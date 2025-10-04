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
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->text('day_of_week')->nullable(); // For weekly recurrence
            $table->text('day_of_month')->nullable(); // For monthly, yearly recurrence
            $table->text('month_of_year')->nullable(); // For yearly recurrence
            $table->date('starts_from')->nullable(); // For Starts from date
            $table->text('number_of_occurrences')->nullable(); // For number of occurrences
            $table->text('completed_occurrences')->nullable(); // For completed occurrences
            $table->boolean('is_active')->default(true); // For status of the recurrence
            $table->dateTime('last_created_at')->nullable(); // Removed the "after" method
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->timestamps();
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_tasks');

    }
};
