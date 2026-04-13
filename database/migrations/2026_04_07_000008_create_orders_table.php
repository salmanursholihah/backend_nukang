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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();     // NKG-20240101-0001
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tukang_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('survey_request_id')->nullable()->constrained()->nullOnDelete();
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);   // komisi platform
            $table->decimal('total_price', 12, 2);
            $table->dateTime('service_date');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'pending',      // menunggu tukang accept
                'accepted',     // tukang accept
                'on_progress',  // sedang dikerjakan
                'completed',    // selesai
                'cancelled',
            ])->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('customer_id');
            $table->index('tukang_id');
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
