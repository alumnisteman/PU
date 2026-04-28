<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'road_id', 'user_id', 'condition_summary', 'notes', 
        'photo', 'latitude', 'longitude', 'inspected_at'
    ];

    protected $casts = [
        'inspected_at' => 'date',
    ];

    public function road()
    {
        return $this->belongsTo(Road::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
