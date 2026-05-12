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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            
            // Siapa yang punya/upload file ini
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Jika NULL maka file Pribadi (My Drive), jika ada ID maka file Grup
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->string('name'); // Nama asli file (contoh: tugas_akhir.zip)
            
            // Gunakan text untuk menyimpan file_id Telegram karena bisa sangat panjang
            $table->text('path'); 
            
            $table->string('mime_type'); // Contoh: application/zip, image/jpeg
            
            // Wajib BigInteger karena file 1GB = 1.073.741.824 bytes (melebihi limit Integer biasa)
            $table->unsignedBigInteger('size'); 
            
            $table->timestamps();

            // Optimasi: Index untuk mempercepat loading file di My Drive atau Group
            $table->index(['user_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};