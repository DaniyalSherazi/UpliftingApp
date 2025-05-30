<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTypeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'current_base_price',
        'current_price_per_km',
        'current_price_per_min',
        'description',
    ];
}
