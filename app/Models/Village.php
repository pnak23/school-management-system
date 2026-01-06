<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'commune_id', 'name_en', 'name_km'
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}


