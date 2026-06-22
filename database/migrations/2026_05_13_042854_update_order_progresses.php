<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Update tabel order_progresses yang sudah ada ──
        Schema::table('order_progresses', function (Blueprint $table) {
            // Hapus kolom photo lama
            $table->dropColumn('photo');

        // Tambah kolom baru
            $table->unsignedTinyInteger('percent')->default(0)->after('description');
            $table->timestamp('reported_at')->nullable()->after('percent');
        });

        // ── 2. Buat tabel baru untuk foto per tahap ──
        Schema::create('order_progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_progress_id')
                  ->constrained('order_progresses')
                  ->onDelete('cascade');
            $table->string('photo_path');  // Path di storage
            $table->string('photo_url');   // URL publik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Rollback: hapus tabel foto
        Schema::dropIfExists('order_progress_photos');

        // Rollback: kembalikan kolom seperti semula
        Schema::table('order_progresses', function (Blueprint $table) {
            $table->dropColumn(['percent', 'reported_at']);
            $table->string('photo')->nullable()->after('description');
        });
    }
};
