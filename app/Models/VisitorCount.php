<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorCount extends Model
{
    protected $fillable = ['page', 'count'];

    public static function incrementFor(string $page): void
    {
        static::firstOrCreate(['page' => $page])->increment('count');
    }

    public static function total(): int
    {
        return static::sum('count');
    }
}
