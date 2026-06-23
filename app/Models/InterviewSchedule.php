<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewSchedule extends Model
{
    protected $fillable = ['application_id', 'scheduled_at', 'venue', 'notes'];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
