<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'slug',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_title_en',
        'seo_description',
        'seo_description_en',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
