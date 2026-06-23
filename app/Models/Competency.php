<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competency extends Model
{
    protected $fillable = [
        'competency_key',
        'competency_name',
        'competency_group',
        'sort_order',
        'description',
    ];

    public function vacancyCompetencies(): HasMany
    {
        return $this->hasMany(VacancyCompetency::class, 'competency_key', 'competency_key');
    }
}
