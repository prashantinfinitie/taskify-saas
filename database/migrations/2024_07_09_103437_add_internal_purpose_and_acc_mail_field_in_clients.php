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
            $table->tinyInteger('internal_purpose')->default('0')->after('email_verified_at');
            $table->tinyInteger('acct_create_mail_sent')->default('1')->after('internal_purpose');
            $table->tinyInteger('email_verification_mail_sent')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('internal_purpose');
            $table->dropColumn('acct_create_mail_sent');
        });
    }
};
