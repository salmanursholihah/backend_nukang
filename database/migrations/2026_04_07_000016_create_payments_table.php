<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // ✅ Tidak pakai constrained() — hindari error tabel belum ada
            // Foreign key ditambah manual di bawah setelah tabel lain siap
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('survey_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('type')->default('order'); // 'order' | 'survey'

            $table->enum('method', [
                'transfer', 'ewallet', 'qris', 'cash', 'credit_card',
            ])->nullable();

            $table->string('payment_channel')->nullable();
            $table->string('bank')->nullable();
            $table->string('va_number')->nullable();

            // ✅ Kolom kunci untuk webhook Midtrans
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('transaction_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('reference_id')->nullable();

            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('pending');

            $table->json('payment_response')->nullable();
            $table->json('midtrans_response')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
