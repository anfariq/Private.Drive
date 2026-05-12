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
        Schema::table('files', function (Blueprint $table) {
            // Pastikan kolom belum ada sebelum menambahkan
            if (!Schema::hasColumn('files', 'group_id')) {
                $table->foreignId('group_id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            // Drop foreign key dulu baru drop kolomnya (Penting di PostgreSQL)
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};