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
        Schema::create('cashbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ارتباط بالمستخدم
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade'); // ارتباط بالدفع
            $table->decimal('cashback_amount', 10, 2); // مبلغ الكاش باك
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // حالة الكاش باك
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbacks');
    }
};
