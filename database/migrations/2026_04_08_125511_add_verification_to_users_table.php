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
        Schema::table('users', function (Blueprint $table) {
            // Kita gunakan logic "hasColumn" agar tidak error jika kolom sudah ada
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false);
            }
            
            if (!Schema::hasColumn('users', 'storage_limit')) {
                // Di Postgres, integer lebih baik untuk limit (dalam GB)
                $table->integer('storage_limit')->default(1); 
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'storage_limit']);
        });
    }
};