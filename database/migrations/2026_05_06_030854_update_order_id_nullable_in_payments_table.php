<?php

// ============================================================
// Jalankan dulu:
// php artisan make:migration make_order_id_nullable_in_payments_table
// Lalu isi file migration yang dibuat dengan kode di bawah:
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Ubah order_id jadi nullable agar survey payment bisa insert tanpa order_id
            $table->foreignId('order_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};
