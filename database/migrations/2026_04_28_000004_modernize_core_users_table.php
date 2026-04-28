<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Backup tabel asli (opsional tapi aman)
        DB::statement('CREATE TABLE IF NOT EXISTS core_users_backup_modernize AS SELECT * FROM core_users');

        // 2. Ubah Mesin dan Collation core_users menjadi Modern
        DB::statement('ALTER TABLE core_users ENGINE = InnoDB');
        DB::statement('ALTER TABLE core_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // 3. Pastikan tipe data kolom kunci sama persis
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        
        Schema::table('core_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });

        // 4. Pasang kembali Foreign Key secara resmi
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('core_users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        DB::statement('ALTER TABLE core_users ENGINE = MyISAM');
    }
};
