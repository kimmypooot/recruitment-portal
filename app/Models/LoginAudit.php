<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAudit extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'event'];

    protected $casts = [
        'user_id' => 'integer',
    ];
}
