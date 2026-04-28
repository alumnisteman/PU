<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'road_id', 'name', 'year', 'budget', 
        'progress', 'status', 'start_date', 'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function road()
    {
        return $this->belongsTo(Road::class);
    }
}
