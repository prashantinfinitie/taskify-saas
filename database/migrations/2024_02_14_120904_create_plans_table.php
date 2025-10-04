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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('max_projects');
            $table->integer('max_clients');
            $table->integer('max_team_members');
            $table->integer('max_workspaces');
            $table->string('plan_type');
            $table->longText('image');
            $table->json('modules');
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('monthly_discounted_price', 10, 2)->nullable();
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->decimal('yearly_discounted_price', 10, 2)->nullable();
            $table->decimal('lifetime_price', 10, 2)->nullable();
            $table->decimal('lifetime_discounted_price', 10, 2)->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
