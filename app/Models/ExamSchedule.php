<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
<<<<<<< HEAD
    protected $fillable = ['application_id', 'exam_type', 'scheduled_at', 'venue', 'notes'];
=======
    protected $fillable = ['application_id', 'scheduled_at', 'venue', 'notes'];
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03

    protected $casts = ['scheduled_at' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
