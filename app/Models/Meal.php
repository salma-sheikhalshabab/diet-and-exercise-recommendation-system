<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;
    protected $fillable = [
        'meal_name',
        'meal_type',
        'description',
        'instructions',
        'ingredients',
        'photo',
        'calories',
        'fat',
        'protein',

    ];
    protected $casts = [

        'ingredients' => 'array',

    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
