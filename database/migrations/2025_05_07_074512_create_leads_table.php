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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('workspace_id')->nullable();

            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('assigned_to')->nullable(); 
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('country_iso_code')->nullable();
            $table->string('country_code')->nullable();

            // Lead Meta
            $table->unsignedBigInteger('source_id')->nullable(); 
            $table->unsignedBigInteger('stage_id')->nullable(); 

            // Created by
            $table->unsignedBigInteger('created_by')->nullable();

            // Professional Details
            $table->string('job_title')->nullable();
            $table->string('company');
            $table->string('industry')->nullable();
            $table->string('website')->nullable();

            // Social Links
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('pinterest')->nullable();

            // Location
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();

            // Optional: Foreign Key Constraints
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('lead_sources')->onDelete('set null');
            $table->foreign('stage_id')->references('id')->on('lead_stages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
