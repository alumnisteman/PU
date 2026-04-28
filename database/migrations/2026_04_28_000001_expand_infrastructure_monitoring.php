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
        // 1. Damage Reports
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('road_asset_id');
            $table->foreign('road_asset_id')->references('id')->on('road_assets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('severity', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->enum('status', ['open', 'progress', 'done'])->default('open');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        // 2. Damage Photos
        Schema::create('damage_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_report_id')->constrained('damage_reports')->onDelete('cascade');
            $table->string('file_path');
            $table->timestamp('taken_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 3. AI Analysis
        Schema::create('ai_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_photo_id')->constrained('damage_photos')->onDelete('cascade');
            $table->string('damage_type'); // pothole, crack, flooding
            $table->float('severity_score'); // 0-1
            $table->float('confidence'); // 0-100
            $table->json('bounding_box')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analysis');
        Schema::dropIfExists('damage_photos');
        Schema::dropIfExists('damage_reports');
    }
};
