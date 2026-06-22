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
      Schema::table('payments', function (Blueprint $table) {
    DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('va','transfer','qris','midtrans')");
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments_method_enum', function (Blueprint $table) {
            //
        });
    }
};
