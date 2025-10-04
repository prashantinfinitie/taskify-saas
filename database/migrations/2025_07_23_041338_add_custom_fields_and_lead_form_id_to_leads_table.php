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
        Schema::table('leads', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('assigned_to');
            $table->foreignId('lead_form_id')->nullable()->constrained('lead_forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lead_form_id']);
            $table->dropColumn('lead_form_id');
            $table->dropColumn('custom_fields');
        });
    }
};
