<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus foreign key lama yang salah arah
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Hubungkan ke tabel yang benar: core_users dengan kolom user_id
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('core_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
