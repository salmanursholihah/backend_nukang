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
        Schema::create('partner_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tukang_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('order_amount', 12, 2);
            $table->decimal('platform_fee', 12, 2);      // potongan komisi
            $table->decimal('amount', 12, 2);             // yang diterima tukang
            $table->enum('status', ['pending', 'settled', 'paid'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
