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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();

        // code: رقم/رمز الطلب للعرض للعميل (فريد)
        $table->string('code')->unique();

        // بيانات العميل
        $table->string('customer_name');
        $table->string('customer_phone');
        $table->string('customer_address');
        $table->text('notes')->nullable();

        // إجمالي الطلب
        $table->decimal('total', 10, 2)->default(0);

        // status: حالة الطلب (مبدئيا pending)
        $table->string('status')->default('pending');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }

};
