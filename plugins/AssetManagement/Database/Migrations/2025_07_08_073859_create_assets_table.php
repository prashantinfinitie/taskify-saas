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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_tag')->unique();
            $table->longText('description')->nullable();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unique(['admin_id', 'asset_tag']);
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('category_id')
                ->constrained('asset_categories')
                ->onDelete('cascade');
            $table->enum('status', ['available', 'lent', 'non-functional', 'lost', 'damaged', 'under-maintenance']);
            $table->date('purchase_date')->nullable();
            $table->string('purchase_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
