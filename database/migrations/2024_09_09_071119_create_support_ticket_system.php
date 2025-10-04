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
        // Create tickets table
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id'); // User who submitted the ticket
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->unsignedBigInteger('priority_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });
        // Create ticket_replies table
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sender_id'); // Admin or superadmin user ID
            $table->enum('sender_role', ['admin', 'superadmin', 'manager']); // Role of the sender
            $table->text('message');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
        // Create ticket_priorities table
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['high', 'medium', 'low']);
            $table->enum('color', ['danger', 'warning', 'success']);
             // Low, Medium, High
            $table->timestamps();
        });

        Schema::create('ticket_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('media_path');
            $table->timestamps();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_media');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('ticket_priorities');

        Schema::dropIfExists('tickets');
    }
};
