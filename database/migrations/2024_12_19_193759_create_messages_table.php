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
            $table->id();
            $table->unsignedBigInteger('conversation_id')->after('id'); // أضف العمود إذا لم يكن موجودًا
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');$table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي أرسل الرسالة
            $table->text('message')->nullable(); // النص أو مسار الصورة
            $table->string('message_type')->nullable(); // نوع الرسالة: نص أو صورة
            $table->timestamp('read_at')->nullable(); // لمعرفة إذا كانت الرسالة قد تم قراءتها
            $table->timestamps();
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
