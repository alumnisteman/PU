<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('roads', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->foreignId('region_id')->nullable()->constrained('regions');
            $table->decimal('length_km', 8, 2)->nullable();
            $table->decimal('width_m', 5, 2)->nullable();
            $table->enum('surface_type', ['aspal', 'beton', 'tanah'])->nullable();
            $table->enum('condition', ['baik', 'sedang', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->integer('condition_score')->default(100);
            $table->enum('traffic_level', ['rendah', 'sedang', 'tinggi'])->default('rendah');
            $table->date('last_survey')->nullable();
            $table->json('geometry')->nullable();
            $table->timestamps();
        });

        Schema::create('road_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_id')->constrained('roads')->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('condition', ['baik', 'rusak'])->default('baik');
            $table->timestamps();
        });

        Schema::create('bridges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('road_id')->nullable()->constrained('roads')->onDelete('set null');
            $table->decimal('length_m', 8, 2)->nullable();
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->timestamps();
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_id')->constrained('roads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('condition_summary')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('inspected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_id')->constrained('roads')->onDelete('cascade');
            $table->string('name');
            $table->integer('year');
            $table->decimal('budget', 15, 2)->nullable();
            $table->integer('progress')->default(0);
            $table->enum('status', ['perencanaan', 'berjalan', 'selesai'])->default('perencanaan');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('bridges');
        Schema::dropIfExists('road_segments');
        Schema::dropIfExists('roads');
        Schema::dropIfExists('regions');
    }
};
