<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// Migration ini HANYA menambahkan kolom yang belum ada.
// Jalankan: php artisan migrate
// ============================================================

return new class extends Migration
{
    public function up(): void
    {
        // ── Tambah kolom ke survey_requests ──────────────────
        Schema::table('survey_requests', function (Blueprint $table) {
            // Biaya dari tukang
            if (!Schema::hasColumn('survey_requests', 'material_cost')) {
                $table->decimal('material_cost', 14, 2)->nullable()->after('estimated_price');
            }
            if (!Schema::hasColumn('survey_requests', 'service_cost')) {
                $table->decimal('service_cost', 14, 2)->nullable()->after('material_cost');
            }
            // Deskripsi kerusakan & catatan dari tukang
            if (!Schema::hasColumn('survey_requests', 'damage_description')) {
                $table->text('damage_description')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('survey_requests', 'tukang_notes')) {
                $table->text('tukang_notes')->nullable()->after('damage_description');
            }
            // Alasan penolakan estimasi dari customer
            if (!Schema::hasColumn('survey_requests', 'rejection_notes')) {
                $table->text('rejection_notes')->nullable()->after('tukang_notes');
            }
            // Status tambahan yang dibutuhkan
            // surveyed | estimation_sent | estimation_approved
            // estimation_rejected | booking_created
            // (jika kolom status sudah ada sebagai VARCHAR, tidak perlu ubah)
        });

        // ── Tambah kolom ke orders (jika belum ada) ──────────
        Schema::table('orders', function (Blueprint $table) {
            // Relasi ke survey_requests
            if (!Schema::hasColumn('orders', 'survey_id')) {
                $table->foreignId('survey_id')
                    ->nullable()
                    ->constrained('survey_requests')
                    ->nullOnDelete()
                    ->after('id');
            }
            // Jadwal pengerjaan dari form booking customer
            if (!Schema::hasColumn('orders', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('survey_id');
            }
            // Tipe order: 'booking' (dari flow estimasi) atau lainnya
            if (!Schema::hasColumn('orders', 'type')) {
                $table->string('type', 30)->default('booking')->after('status');
            }
        });

        // ── Tambah kolom ke order_details (jika belum ada) ───
        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'material_cost')) {
                $table->decimal('material_cost', 14, 2)->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('order_details', 'service_cost')) {
                $table->decimal('service_cost', 14, 2)->nullable()->after('material_cost');
            }
            if (!Schema::hasColumn('order_details', 'total_cost')) {
                $table->decimal('total_cost', 14, 2)->nullable()->after('service_cost');
            }
            if (!Schema::hasColumn('order_details', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('total_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('survey_requests', function (Blueprint $table) {
            $table->dropColumn([
                'material_cost', 'service_cost',
                'damage_description', 'tukang_notes', 'rejection_notes',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['survey_id']);
            $table->dropColumn(['survey_id', 'scheduled_date', 'type']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['material_cost', 'service_cost', 'total_cost', 'duration_days']);
        });
    }
};
