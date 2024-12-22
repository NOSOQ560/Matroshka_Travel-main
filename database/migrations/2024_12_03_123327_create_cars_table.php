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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('type')->comment('business , normal');
            $table->tinyInteger('passenger_from');
            $table->tinyInteger('passenger_to');
            $table->tinyInteger('package_from');
            $table->tinyInteger('package_to')->nullable();
            $table->decimal('airport_to_town');
            $table->decimal('hour_in_town');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
