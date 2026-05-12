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
        Schema::table('groups', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada agar tidak error saat migrate
            if (!Schema::hasColumn('groups', 'creator_id')) {
                $table->foreignId('creator_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // Urutan hapus di Postgres: Hapus Foreign Key dulu, baru kolomnya
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
    }
};