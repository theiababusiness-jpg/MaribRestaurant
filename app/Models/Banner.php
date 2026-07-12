<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banners';

    /**
     * الحقول المسموح تعبئتها (متوافقة 100% مع قاعدة البيانات)
     */
    protected $fillable = [
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'image_path',
        'link_url',
        'link_text',      // النص العربي (الأساسي)
        'link_text_en',   // النص الإنجليزي
        'link_type',
        'product_id',
        'is_active',
        'sort_order',
        'start_at',
        'end_at',
    ];

    /**
     * تحويل أنواع البيانات تلقائيًا
     */
    protected $casts = [
        'is_active' => 'boolean',
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
    ];

    /* ================= Relations ================= */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /* ================= Scopes ================= */

   /* ================= Scopes ================= */

/**
 * العروض التي يجب عرضها للمستخدم
 * - مفعل
 * - بدأ وقتها (أو بدون بداية)
 * - لم تنتهِ (أو بدون نهاية)
 */
public function scopeActive($query)
{
    $now = now()->timezone(config('app.timezone'));

    return $query
        ->where('is_active', 1)
        ->where(function ($q) use ($now) {
            $q->whereNull('start_at')
              ->orWhere('start_at', '<=', $now);
        })
        ->where(function ($q) use ($now) {
            $q->whereNull('end_at')
              ->orWhere('end_at', '>=', $now);
        });
}

}
