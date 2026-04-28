<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('unit', 20);
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });

        Schema::create('road_materials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('road_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('volume', 10, 2);
            $table->timestamps();

            $table->foreign('road_id')->references('id')->on('road_assets')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials');
        });
    }

    public function down()
    {
        Schema::dropIfExists('road_materials');
        Schema::dropIfExists('materials');
    }
};
