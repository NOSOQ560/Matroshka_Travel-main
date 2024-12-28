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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ارتباط بالمستخدم
            $table->string('session_id'); // معرف الجلسة من Stripe
            $table->decimal('amount', 10, 2); // المبلغ
            $table->string('currency', 10); // العملة
            $table->string('product_name')->nullable(); // اسم المنتج
            $table->text('description')->nullable(); // وصف المنتج
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending'); // حالة الدفع
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
