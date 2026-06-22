<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Cek dan tambah kolom yang belum ada saja
            if (!Schema::hasColumn('payments', 'payable_id')) {
                $table->unsignedBigInteger('payable_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('payments', 'payable_type')) {
                $table->string('payable_type')->nullable()->after('payable_id');
            }
            if (!Schema::hasColumn('payments', 'survey_id')) {
                $table->unsignedBigInteger('survey_id')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('payments', 'type')) {
                $table->string('type')->default('order')->after('survey_id');
            }
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->default('bank_transfer')->after('method');
            }
            if (!Schema::hasColumn('payments', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->unique()->after('snap_token');
            }
            if (!Schema::hasColumn('payments', 'midtrans_response')) {
                $table->json('midtrans_response')->nullable()->after('payment_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payable_id',
                'payable_type',
                'survey_id',
                'type',
                'payment_type',
                'midtrans_order_id',
                'midtrans_response',
            ]);
        });
    }
};
