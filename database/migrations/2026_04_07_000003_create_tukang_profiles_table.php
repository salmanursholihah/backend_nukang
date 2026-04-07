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
        Schema::create('tukang_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();   // lokasi domisili
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('id_card_photo')->nullable();      // foto KTP
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_jobs')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_available')->default(true);   // toggle online/offline
            $table->decimal('radius_km', 5, 2)->default(10); // jangkauan layanan
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
