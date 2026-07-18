<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'seo_title')) {
                $table->string('seo_title')->nullable();
            }

            if (! Schema::hasColumn('categories', 'seo_title_en')) {
                $table->string('seo_title_en')->nullable();
            }

            if (! Schema::hasColumn('categories', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }

            if (! Schema::hasColumn('categories', 'seo_description_en')) {
                $table->text('seo_description_en')->nullable();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_special_message')) {
                $table->boolean('has_special_message')->default(false);
            }

            if (! Schema::hasColumn('products', 'special_message')) {
                $table->text('special_message')->nullable();
            }

            if (! Schema::hasColumn('products', 'special_message_en')) {
                $table->text('special_message_en')->nullable();
            }

            if (! Schema::hasColumn('products', 'seo_title')) {
                $table->string('seo_title')->nullable();
            }

            if (! Schema::hasColumn('products', 'seo_title_en')) {
                $table->string('seo_title_en')->nullable();
            }

            if (! Schema::hasColumn('products', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }

            if (! Schema::hasColumn('products', 'seo_description_en')) {
                $table->text('seo_description_en')->nullable();
            }
        });

        Schema::table('banners', function (Blueprint $table) {
            if (! Schema::hasColumn('banners', 'title_en')) {
                $table->string('title_en')->nullable();
            }

            if (! Schema::hasColumn('banners', 'subtitle_en')) {
                $table->string('subtitle_en')->nullable();
            }

            if (! Schema::hasColumn('banners', 'link_text_en')) {
                $table->string('link_text_en')->nullable();
            }

            if (! Schema::hasColumn('banners', 'link_type')) {
                $table->string('link_type')->default('none');
            }

            if (! Schema::hasColumn('banners', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            }

            if (! Schema::hasColumn('banners', 'start_at')) {
                $table->dateTime('start_at')->nullable();
            }

            if (! Schema::hasColumn('banners', 'end_at')) {
                $table->dateTime('end_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'end_at')) {
                $table->dropColumn('end_at');
            }

            if (Schema::hasColumn('banners', 'start_at')) {
                $table->dropColumn('start_at');
            }

            if (Schema::hasColumn('banners', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }

            if (Schema::hasColumn('banners', 'link_type')) {
                $table->dropColumn('link_type');
            }

            if (Schema::hasColumn('banners', 'link_text_en')) {
                $table->dropColumn('link_text_en');
            }

            if (Schema::hasColumn('banners', 'subtitle_en')) {
                $table->dropColumn('subtitle_en');
            }

            if (Schema::hasColumn('banners', 'title_en')) {
                $table->dropColumn('title_en');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'seo_description_en')) {
                $table->dropColumn('seo_description_en');
            }

            if (Schema::hasColumn('products', 'seo_description')) {
                $table->dropColumn('seo_description');
            }

            if (Schema::hasColumn('products', 'seo_title_en')) {
                $table->dropColumn('seo_title_en');
            }

            if (Schema::hasColumn('products', 'seo_title')) {
                $table->dropColumn('seo_title');
            }

            if (Schema::hasColumn('products', 'special_message_en')) {
                $table->dropColumn('special_message_en');
            }

            if (Schema::hasColumn('products', 'special_message')) {
                $table->dropColumn('special_message');
            }

            if (Schema::hasColumn('products', 'has_special_message')) {
                $table->dropColumn('has_special_message');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'seo_description_en')) {
                $table->dropColumn('seo_description_en');
            }

            if (Schema::hasColumn('categories', 'seo_description')) {
                $table->dropColumn('seo_description');
            }

            if (Schema::hasColumn('categories', 'seo_title_en')) {
                $table->dropColumn('seo_title_en');
            }

            if (Schema::hasColumn('categories', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
        });
    }
};
