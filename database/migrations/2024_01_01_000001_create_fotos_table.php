<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fotos', function (Blueprint $table) {
            $table->id();
            $table->string('caption');
            $table->string('url');           // URL Cloudinary (HTTPS)
            $table->string('thumbnail_url'); // URL thumbnail kecil untuk grid
            $table->string('public_id');     // Cloudinary public_id (untuk delete)
            $table->string('uploader')->nullable(); // nama pengirim (opsional)
            $table->unsignedBigInteger('file_size')->nullable(); // ukuran file bytes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotos');
    }
};
