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
        Schema::create('survey_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tukang_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->text('address');
            $table->dateTime('survey_date')->nullable();
            $table->decimal('survey_fee', 12, 2)->nullable();
            $table->decimal('estimated_price', 12, 2)->nullable();
            $table->integer('estimated_days')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'requested',
                'accepted',
                'rejected',
                'survey_priced',
                'estimated',
                'approved',
                'cancelled'
            ])->default('requested');
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
