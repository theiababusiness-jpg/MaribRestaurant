<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // مهم
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model: User
 * يستخدم لتسجيل دخول المدير فقط
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable; // مهم

    // use Notifiable;

    /**
     * الأعمدة المسموح حفظها (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * الأعمدة المخفية عند الإرجاع
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
