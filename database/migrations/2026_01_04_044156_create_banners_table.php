<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // title: عنوان العرض (مثال: أكلات يمنية أصيلة)
            $table->string('title');

            // subtitle: وصف/سطر إضافي تحت العنوان (اختياري)
            $table->string('subtitle')->nullable();

            // image_path: مسار صورة العرض داخل public
            $table->string('image_path');

            // is_active: هل العرض مفعل ويظهر للمستخدم؟
            $table->boolean('is_active')->default(true);

            // sort_order: ترتيب ظهور العروض (0 = أول واحد)
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
