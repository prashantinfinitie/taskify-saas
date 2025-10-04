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
        Schema::table('user_client_preferences', function (Blueprint $table) {
            $table->longText('enabled_notifications')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_client_preferences', function (Blueprint $table) {
            $table->dropColumn('enabled_notifications');
        });
    }
};
