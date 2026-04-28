<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Road extends Model
{
    use Searchable, HasSpatial;

    protected $fillable = [
        'code', 'name', 'region_id', 'length_km', 'width_m', 
        'surface_type', 'condition', 'condition_score', 
        'traffic_level', 'last_survey', 'geometry',
        'damage_type', 'damage_details', 'lat', 'lng', 'geom'
    ];

    protected $casts = [
        'geometry' => 'array',
        'damage_details' => 'array',
        'last_survey' => 'date',
        'geom' => LineString::class,
    ];

    // Data for Meilisearch
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'condition' => $this->condition,
            'surface_type' => $this->surface_type,
            'length_km' => $this->length_km,
        ];
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function bridges()
    {
        return $this->hasMany(Bridge::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }

    public function getPriorityScoreAttribute()
    {
        $score = 0;

        switch ($this->condition) {
            case 'rusak_berat': $score += 50; break;
            case 'rusak_ringan': $score += 30; break;
            case 'sedang': $score += 20; break;
            case 'baik': $score += 5; break;
        }

        switch ($this->traffic_level) {
            case 'tinggi': $score += 30; break;
            case 'sedang': $score += 15; break;
            case 'rendah': $score += 5; break;
        }

        $score += min(($this->length_km * 2), 10);
        return $score;
    }
}
