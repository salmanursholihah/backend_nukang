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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', [
                'transfer',
                'ewallet',
                'qris',
                'cash',
                'credit_card',
            ])->nullable();
            $table->string('payment_channel')->nullable();   // BCA, GoPay, OVO, dll
            $table->string('reference_id')->nullable();      // dari payment gateway
            $table->string('snap_token')->nullable();        // Midtrans Snap
            $table->json('payment_response')->nullable();    // raw response gateway
            $table->enum('status', [
                'unpaid',
                'pending',
                'paid',
                'failed',
                'refunded',
            ])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
