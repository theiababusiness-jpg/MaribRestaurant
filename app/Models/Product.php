<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'name_en',
        'description',
        'description_en',
        'price',
        'has_special_message',
        'special_message',
        'special_message_en',
        'slug',
        'is_active',
        'sort_order',
        'image_path',
        'seo_title',
        'seo_title_en',
        'seo_description',
        'seo_description_en',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'has_special_message' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups()
    {
        return $this->belongsToMany(OptionGroup::class, 'option_group_product')
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
