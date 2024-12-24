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
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // العمود الأساسي
            $table->unsignedBigInteger('conversation_id'); // معرف المحادثة
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade'); // العلاقة مع جدول المحادثات
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // العلاقة مع جدول المستخدمين
            $table->text('message')->nullable(); // النص أو مسار الصورة
            $table->string('message_type')->nullable(); // نوع الرسالة: نص أو صورة
            $table->timestamp('read_at')->nullable(); // وقت قراءة الرسالة
            $table->timestamps(); // وقت الإنشاء والتحديث
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
