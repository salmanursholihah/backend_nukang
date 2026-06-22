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
       Schema::table('survey_requests', function (Blueprint $table) {
            // Deskripsi kerusakan yang ditemukan tukang di lokasi
            $table->text('damage_description')->nullable()->after('tukang_notes');

            // Bahan/material yang dibutuhkan (format bebas, tukang isi sebagai teks)
            $table->text('materials_needed')->nullable()->after('damage_description');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_requests', function (Blueprint $table) {
            $table->dropColumn(['damage_description', 'materials_needed']);
        });
    }
};
