<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAnalysis extends Model
{
    protected $table = 'ai_analysis';

    protected $fillable = [
        'damage_photo_id', 'damage_type', 'severity_score', 
        'confidence', 'bounding_box', 'processed_at'
    ];

    protected $casts = [
        'bounding_box' => 'array',
        'processed_at' => 'datetime',
    ];

    public function photo()
    {
        return $this->belongsTo(DamagePhoto::class, 'damage_photo_id');
    }
}
