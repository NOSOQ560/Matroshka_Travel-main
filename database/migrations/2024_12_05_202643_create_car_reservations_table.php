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
        Schema::create('car_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('pickup_location')->nullable();
            $table->string('arrival_location')->nullable();
            $table->timestamp('departing')->nullable();
            $table->timestamp('returning')->nullable();
            $table->tinyInteger('packages')->default(0);
            $table->tinyInteger('childrens')->default(0);
            $table->json('childAge')->nullable();
            $table->tinyInteger('adults')->default(1);
            $table->string('trip_type')->nullable()->comment('accommodation , recreational');
            $table->longText('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_reservations');
    }
};
