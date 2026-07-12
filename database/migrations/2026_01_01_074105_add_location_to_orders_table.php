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
        Schema::table('orders', function (Blueprint $table) {
            // lat/lng: إحداثيات الموقع المختار من الخريطة
            $table->decimal('lat', 10, 7)->nullable()->after('customer_address');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');

            // map_address: عنوان نصي يرجع من البحث/الخريطة (اختياري)
            $table->string('map_address')->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'map_address']);
        });
    }

};
