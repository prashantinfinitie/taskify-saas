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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('country_code')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->date('dob')->nullable()->change();
            $table->date('doj')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('zip')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company')->change();
            $table->string('phone')->change();
            $table->string('country_code')->change();
            $table->string('password')->change();
            $table->date('dob')->change();
            $table->date('doj')->change();
            $table->string('address')->change();
            $table->string('city')->change();
            $table->string('state')->change();
            $table->string('country')->change();
            $table->string('zip')->change();
        });
    }
};
