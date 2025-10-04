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
        Schema::table('contracts', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
        Schema::table('estimates_invoices', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->integer('zip_code')->nullable()->change();
            $table->bigInteger('phone')->nullable()->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('contracts', function (Blueprint $table) {
            $table->string('description')->change();
        });
        Schema::table('estimates_invoices', function (Blueprint $table) {
            $table->string('address')->change();
            $table->string('city')->change();
            $table->string('state')->change();
            $table->string('country')->change();
            $table->integer('zip_code')->change();
            $table->bigInteger('phone')->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('description')->change();
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->longText('description')->change();
        });
    }
};
