<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'muscle_name',
        'exercise_name',
        'exercise_image',
        'gif',
        'description',
        'calories',
        'duration',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
