<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique(); // ID unik untuk Midtrans
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('gross_amount', 15, 2);
            $table->string('bank'); // bca, bni, bri, mandiri, dll
            $table->string('va_number')->nullable();
            $table->string('payment_status')->default('pending');
            // pending | settlement | expire | cancel | deny
            $table->string('transaction_id')->nullable(); // dari Midtrans
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('midtrans_response')->nullable(); // simpan full response
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
