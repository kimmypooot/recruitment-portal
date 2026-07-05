<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'category',
        'subject',
        'greeting',
        'body',
        'action_text',
        'action_url',
        'action_locked',
        'footer',
        'placeholders',
        'sample_data',
        'is_active',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'sample_data' => 'array',
        'action_locked' => 'boolean',
        'is_active' => 'boolean',
    ];
}
