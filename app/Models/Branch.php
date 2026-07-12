<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'address',
        'address_en',
        'phone',
        'whatsapp_number',
        'google_maps_url',
        'lat',
        'lng',
        'is_active',
        'pickup_enabled',
        'delivery_enabled',
        'sort_order',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_active' => 'boolean',
        'pickup_enabled' => 'boolean',
        'delivery_enabled' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
