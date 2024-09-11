<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Progression;



class ProgressionController extends Controller
{

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

        // Get start and end dates of the current week
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();

        // Fetch daily progressions for the current week
        $progressions = Progression::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->selectRaw('
            date,
            SUM(total_calories) as total_calories,
            SUM(total_fat) as total_fat,
            SUM(total_protein) as total_protein
        ')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Ensure all days of the week are included, even if there's no data
        $weekDays = [];
        $currentDate = Carbon::parse($startOfWeek);

        while ($currentDate->lte(Carbon::parse($endOfWeek))) {
            $dateString = $currentDate->toDateString();
            $dayProgress = $progressions->firstWhere('date', $dateString);

            $weekDays[] = [
                'date' => $dateString,
                'total_calories' => $dayProgress->total_calories ?? 0,
                'total_fat' => $dayProgress->total_fat ?? 0,
                'total_protein' => $dayProgress->total_protein ?? 0,
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'weekly_progress' => $weekDays
        ]);
    }
    public function get_Monthly_Progress()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        // Get the start and end dates for the last four weeks
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfFourWeeksAgo = Carbon::now()->subWeeks(3)->startOfWeek();

        // Initialize arrays to hold the weekly data
        $weeklyCalories = [];
        $weeklyFat = [];
        $weeklyProtein = [];

        $currentWeekStart = $startOfFourWeeksAgo->copy();
        $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();

        // Loop through each week within the last four weeks
        while ($currentWeekStart->lte($endOfWeek)) {
            // Fetch progressions for the current week
            $progression = Progression::where('user_id', $user->id)
                ->whereBetween('date', [$currentWeekStart->toDateString(), $currentWeekEnd->toDateString()])
                ->selectRaw('
                SUM(total_calories) as total_calories,
                SUM(total_fat) as total_fat,
                SUM(total_protein) as total_protein
            ')
                ->first();

            // Add the weekly data to the arrays
            $weeklyCalories[] = [
                'week_start' => $currentWeekStart->toDateString(),
                'week_end' => $currentWeekEnd->toDateString(),
                'total_calories' => $progression->total_calories ?? 0
            ];

            $weeklyFat[] = [
                'week_start' => $currentWeekStart->toDateString(),
                'week_end' => $currentWeekEnd->toDateString(),
                'total_fat' => $progression->total_fat ?? 0
            ];

            $weeklyProtein[] = [
                'week_start' => $currentWeekStart->toDateString(),
                'week_end' => $currentWeekEnd->toDateString(),
                'total_protein' => $progression->total_protein ?? 0
            ];

            // Move to the next week
            $currentWeekStart->addWeek();
            $currentWeekEnd->addWeek();
        }

        return response()->json([
            'calories_progress' => $weeklyCalories,
            'fat_progress' => $weeklyFat,
            'protein_progress' => $weeklyProtein
        ]);}
}
