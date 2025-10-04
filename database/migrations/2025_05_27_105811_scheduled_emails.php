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
        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreignId('email_template_id')
                ->nullable() 
                ->constrained('email_templates') // Add the foreign key constraint
                ->onDelete('cascade'); // Specify the behavior on delete
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('to_email');
            $table->string('subject');
            $table->text('body');
            $table->json('placeholders')->nullable(); // For dynamic content
            $table->json('attachments')->nullable(); // For file attachments
            $table->dateTime('scheduled_at')->nullable(); // Scheduled send time
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending'); // Status tracking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');

    }
};
