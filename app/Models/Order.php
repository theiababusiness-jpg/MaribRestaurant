<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'code',
        'branch_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'items_subtotal',
        'delivery_fee',
        'delivery_distance_km',
        'total',
        'status',
        'lat',
        'lng',
        'map_address',
        'is_seen',
        'fulfillment_method',
        'payment_method',
        'payment_provider',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'items_subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'delivery_distance_km' => 'decimal:2',
        'total' => 'decimal:2',
        'is_seen' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
