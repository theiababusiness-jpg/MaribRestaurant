<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Refactor categories columns:
 * - نضيف: name, description
 * - ننسخ البيانات من name_ar إلى name (إذا موجودة)
 * - نحذف name_ar و name_en (و description_ar/description_en إن وجدت)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) إضافة الأعمدة الجديدة إذا لم تكن موجودة
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });

        // 2) نسخ البيانات القديمة إلى الجديدة (لو كانت الأعمدة القديمة موجودة)
        if (Schema::hasColumn('categories', 'name_ar')) {
            DB::statement("UPDATE categories SET name = COALESCE(name, name_ar) WHERE name IS NULL OR name = ''");
        }

        if (Schema::hasColumn('categories', 'description_ar')) {
            DB::statement("UPDATE categories SET description = COALESCE(description, description_ar) WHERE description IS NULL OR description = ''");
        }

        // 3) حذف الأعمدة القديمة (لو موجودة)
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
            if (Schema::hasColumn('categories', 'name_en')) {
                $table->dropColumn('name_en');
            }
            if (Schema::hasColumn('categories', 'description_ar')) {
                $table->dropColumn('description_ar');
            }
            if (Schema::hasColumn('categories', 'description_en')) {
                $table->dropColumn('description_en');
            }
        });
    }

    public function down(): void
    {
        // رجوع بسيط: نعيد name_ar/name_en (بدون استرجاع محتوى مضبوط)
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'name_ar')) {
                $table->string('name_ar')->nullable();
            }
            if (!Schema::hasColumn('categories', 'name_en')) {
                $table->string('name_en')->nullable();
            }
        });
    }
};
