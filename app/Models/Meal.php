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
        'photo',
        'calories',
        'fat',
        'protein',
        //'carbs',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}
