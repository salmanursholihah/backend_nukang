<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();        // ID order dari sistem Anda
            $table->string('va_number', 30)->unique();   // Nomor VA BCA
            $table->string('customer_name', 100);
            $table->string('customer_email', 150)->nullable();
            $table->decimal('amount', 15, 2);            // Nominal tagihan
            $table->enum('status', ['PENDING', 'PAID', 'EXPIRED', 'FAILED'])
                  ->default('PENDING');
            $table->timestamp('paid_at')->nullable();    // Kapan dibayar
            $table->timestamp('expired_at')->nullable(); // Kapan VA kadaluarsa
            $table->json('bca_response')->nullable();    // Raw response create VA
            $table->json('callback_payload')->nullable();// Raw callback pembayaran
            $table->timestamps();

            $table->index(['va_number', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_accounts');
    }
};
