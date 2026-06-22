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
        Schema::table('withdrawals', function (Blueprint $table) {
            // Jika kolom reference_id belum ada, tambahkan
            if (!Schema::hasColumn('withdrawals', 'reference_id')) {
                $table->string('reference_id')->nullable()->after('notes');
            }

            // Kolom Iris baru
            $table->string('iris_reference_no')
                ->nullable()
                ->after('reference_id')
                ->comment('Reference number dari Midtrans Iris createPayout response');

            $table->string('iris_status')
                ->nullable()
                ->after('iris_reference_no')
                ->comment('Status dari Iris: queued | processed | failed');

            $table->json('iris_response')
                ->nullable()
                ->after('iris_status')
                ->comment('Raw JSON response dari Iris untuk debugging');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['iris_reference_no', 'iris_status', 'iris_response']);
        });
    }
};
