<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->longText('description')->nullable();
             $table->string('asset_tag');
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

        // Insert permissions manually
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->insert([
                ['name' => 'manage_assets', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'create_assets', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'edit_assets', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'delete_assets', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::table('permissions')->whereIn('name', ['manage_assets', 'create_assets', 'edit_assets', 'delete_assets'])->delete();
        Schema::dropIfExists('assets');
    }
};

