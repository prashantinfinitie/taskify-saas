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

        Schema::create('user_client_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 56);
            $table->string('table_name');
            $table->json('visible_columns')->nullable();
            $table->string('default_view')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_client_preferences');
    }
};
