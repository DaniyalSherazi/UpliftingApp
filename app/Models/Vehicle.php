<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;


    protected $fillable = [
        'vehicle_of',
        'number_plate',
        'registration_certificate',
        'insurance_validity',
        'vehicle_insurance',
        'make',
        'model',
        'year',
        'color',
        'vehicle_type_rate_id',
    ];
}
