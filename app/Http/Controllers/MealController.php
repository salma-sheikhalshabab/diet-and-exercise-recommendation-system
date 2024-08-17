<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Progression;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealController extends Controller
{
    public function getMeals(){

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

       $meals = $user->meals;

        return response()->json( $meals,200);
}

}
