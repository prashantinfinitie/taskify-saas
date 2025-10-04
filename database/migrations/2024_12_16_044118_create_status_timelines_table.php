<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_timelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id'); // For projects or tasks
            $table->string('entity_type'); // 'project' or 'task'
            $table->string('status');
            $table->string('previous_status')->nullable(); // No "after()" method here
            $table->string('new_color')->nullable();
            $table->string('old_color')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['entity_id', 'entity_type']); // For quick lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_timelines');
    }
};
