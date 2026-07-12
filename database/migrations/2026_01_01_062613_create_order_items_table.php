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
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();

        // ربط بالطلب
        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

        // ربط بالمنتج (اختياري: نخليه nullable حتى لو المنتج انحذف يبقى السجل)
        $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

        // snapshot: نخزن الاسم وقت الطلب (حتى لو تغير لاحقا)
        $table->string('product_name');

        // qty: الكمية
        $table->unsignedInteger('qty')->default(1);

        // unit_price: سعر الوحدة (مع التخصيصات)
        $table->decimal('unit_price', 10, 2)->default(0);

        // line_total: إجمالي هذا السطر
        $table->decimal('line_total', 10, 2)->default(0);

        // options_json: التخصيصات المختارة (نخزنها JSON)
        $table->json('options_json')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }

};
