<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class DamageReport extends Model
{
    use Searchable, HasSpatial;

    protected $fillable = [
        'road_id', 'damage_type', 'geom', 'accuracy', 'source', 'details'
    ];

    protected $casts = [
        'geom' => Point::class,
        'details' => 'array',
        'accuracy' => 'float',
    ];

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'damage_type' => $this->damage_type,
            'source' => $this->source,
            'road_id' => $this->road_id,
            'road_name' => $this->road ? $this->road->name : null,
        ];
    }

    public function road()
    {
        return $this->belongsTo(Road::class);
    }
}
