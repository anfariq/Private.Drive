<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel groups
            $table->foreignId('group_id')
                  ->constrained()
                  ->onDelete('cascade');
                  
            // Relasi ke tabel users
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Tambahkan Index unik agar satu user tidak bisa masuk ke grup yang sama dua kali
            $table->unique(['group_id', 'user_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_user');
    }
};