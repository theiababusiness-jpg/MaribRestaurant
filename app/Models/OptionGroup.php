<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionGroup extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'admin_note',
        'is_required',
        'is_multiple',
        'min_select',
        'max_select',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_multiple' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(Option::class)
            ->where('is_active', 1)
            ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'option_group_product')
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order');
    }
}
