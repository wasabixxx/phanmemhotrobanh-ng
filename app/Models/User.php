<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Kế thừa từ đây
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable // Sử dụng Authenticatable ở đây
{
    use HasFactory, Notifiable;

    protected $table = 'users'; // Nếu bạn đã đặt tên bảng khác

    protected $fillable = [
        'username',
        'password',
        'name',
        'phone',
        'email',
        'role_id',
        'shift_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Nếu bạn sử dụng email_verified_at
    protected $dates = [
        'email_verified_at',
        'created_at',
        'updated_at',
    ];
}
