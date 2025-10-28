<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'expires_at',
        'refresh_token',
        'access_token',
        'service_id',
        'user_id'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}
