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
    Schema::create('products', function (Blueprint $table) {
        $table->id();

        // ربط الطبق بالتصنيف
        $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

        // اسم الطبق (العربي)
        $table->string('name');

        // وصف اختياري
        $table->text('description')->nullable();

        // السعر
        $table->decimal('price', 10, 2)->default(0);

        // رابط SEO مثل: chicken-rice
        $table->string('slug')->unique();

        // اظهار/اخفاء الطبق بدون حذف
        $table->boolean('is_active')->default(1);

        // ترتيب يدوي
        $table->unsignedInteger('sort_order')->default(0);

        // صورة لاحقا (مسار الصورة)
        $table->string('image_path')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
