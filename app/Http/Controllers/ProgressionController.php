<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Progression;
use App\Models\Meal;


class ProgressionController extends Controller
{

     public function log_Exercise(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'message' => "Unauthorized"
        ], 401);
    }
    $exercise = Exercise::find($request->input('exercise_id'));

    if (!$exercise) {
        return response()->json(['message' => 'Exercise not found'], 404);
    }

    $today = Carbon::today()->toDateString();

    $progression = Progression::firstOrCreate(
        ['user_id' => $user->id, 'date' => $today],
        ['total_calories' => 0, 'total_fat' => 0, 'total_protein' => 0]
    );

    $progression->decrement('total_calories', $exercise->calories);

    return response()->json(['message' => 'Exercise logged successfully']);
}

    public function log_Meal(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $meal = Meal::find($request->input('meal_id'));

        if (!$meal) {
            return response()->json(['message' => 'Meal not found'], 404);
        }

        // Get today's date
        $today = Carbon::today()->toDateString();

        // Update the progressions table
        $progression = Progression::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['total_calories' => 0, 'total_fat' => 0, 'total_protein' => 0]
        );

        $progression->increment('total_calories', $meal->calories);
        $progression->increment('total_fat', $meal->fat);
        $progression->increment('total_protein', $meal->protein);

        return response()->json(['message' => 'Meal logged successfully']);
    }

    public function get_Daily_Progress()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $today = Carbon::today()->toDateString();

        $progression = Progression::where('user_id', $user->id)
            ->where('date', $today)
            ->selectRaw('
                SUM(total_calories) as daily_calories,
                SUM(total_fat) as daily_fat,
                SUM(total_protein) as daily_protein
            ')
            ->first();

        return response()->json($progression);
    }
    public function get_Weekly_Progress()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();

        $progression = Progression::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->selectRaw('
            SUM(total_calories) as weekly_calories,
            SUM(total_fat) as weekly_fat,
            SUM(total_protein) as weekly_protein
        ')
            ->first();

        return response()->json($progression);
    }

    public function get_Monthly_Progress()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $progression = Progression::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->selectRaw('
            SUM(total_calories) as monthly_calories,
            SUM(total_fat) as monthly_fat,
            SUM(total_protein) as monthly_protein
        ')
            ->first();

        return response()->json($progression);
    }
}
