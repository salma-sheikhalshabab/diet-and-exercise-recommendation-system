<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progression extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'date',
        'total_calories',
        'total_fat',
        'total_protein',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
