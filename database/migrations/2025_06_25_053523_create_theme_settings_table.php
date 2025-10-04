<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateThemeSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme_name')->default('new'); // 'old' or 'new'
            $table->boolean('is_active')->default(true);
            $table->json('theme_config')->nullable();
            $table->timestamps();
        });

        // Insert default theme
        DB::table('theme_settings')->insert([
            'theme_name' => 'new',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('theme_settings');
    }
}
