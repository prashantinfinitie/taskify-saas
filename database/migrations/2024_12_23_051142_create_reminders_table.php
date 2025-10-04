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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->morphs('remindable'); // Adds `remindable_id` and `remindable_type` columns
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly']);
            $table->integer('frequency_value')->nullable();
            $table->integer('day_of_week')->nullable(); // Used for weekly reminders
            $table->integer('day_of_month')->nullable(); // Used for monthly reminders
            $table->time('time_of_day');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
