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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('primary');
            $table->longText('description')->nullable();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->timestamps();
        });

        if (Schema::hasTable('permissions')) {

            DB::table('permissions')->insert([
                ['name' => 'manage_asset_categories', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'create_asset_categories', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'edit_asset_categories', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'delete_asset_categories', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::table('permissions')->whereIn('name', [
            'manage_asset_categories',
            'create_asset_categories',
            'edit_asset_categories',
            'delete_asset_categories'
        ])->delete();

        Schema::dropIfExists('asset_categories');
    }
};

