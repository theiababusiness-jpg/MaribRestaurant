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
    Schema::create('option_groups', function (Blueprint $table) {
        $table->id();

        // اسم المجموعة مثل: نوع الرز / إضافات
        $table->string('name');

        // هل المجموعة اجبارية؟
        $table->boolean('is_required')->default(0);

        // اختيار واحد أو متعدد
        $table->boolean('is_multiple')->default(0);

        // ترتيب العرض
        $table->unsignedInteger('sort_order')->default(0);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_groups');
    }
};
