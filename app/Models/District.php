<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id', 'name_en', 'name_km', 'type'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function communes()
    {
        return $this->hasMany(Commune::class);
    }
}


