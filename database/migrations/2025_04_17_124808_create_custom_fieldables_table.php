<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomFieldablesTable extends Migration
{
    public function up()
    {
        Schema::create('custom_fieldables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_field_id');
            $table->unsignedBigInteger('custom_fieldable_id');
            $table->string('custom_fieldable_type');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->foreign('custom_field_id')
                  ->references('id')
                  ->on('custom_fields')
                  ->onDelete('cascade');
                  
            $table->index(['custom_fieldable_id', 'custom_fieldable_type'], 'cf_fieldable_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_fieldables');
    }
}