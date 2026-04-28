<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamagePhoto extends Model
{
    protected $fillable = [
        'damage_report_id', 'file_path', 'taken_at', 'uploaded_by'
    ];

    public function report()
    {
        return $this->belongsTo(DamageReport::class, 'damage_report_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function aiAnalysis()
    {
        return $this->hasOne(AiAnalysis::class, 'damage_photo_id');
    }
}
