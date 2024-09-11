<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Progression;
use App\Models\UserExerciseSelection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ExerciseController extends Controller
{
    public function getExerciseRecommendation()
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        // Prepare the user information to be sent to the Flask server
        $userInfo = [
            'height' => $user->height,
            'weight' => $user->weight,
            'activity_level' => $user->activity_level,
            'disease' => $user->disease,
        ];

        // Send the user information to the Flask server
        $response = Http::post('http://127.0.0.1:5001/exercise-recommendation', $userInfo);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();
            $exercises = $data['exercises'];

            // Store meals in the database
            foreach ($exercises as $exerciseData) {
                $exercise= Exercise::firstOrCreate([
                    'muscle_name' => $exerciseData['muscle'],
                    'exercise_name' => $exerciseData['name'],
                    'description' => $exerciseData['description'],
                     'gif' => $exerciseData['image'],
                     'sets'=>$exerciseData['sets'],
                    'Reps_per_set'=>$exerciseData['Reps per set'],
                    'calories' => $exerciseData['calories burned'],
                    'Duration per set' => $exerciseData['Duration per set'],

                ]);

                // Check if the user already has this exercise
                if (!$user->exercises()->where('exercise_id', $exercise->id)->exists()) {
                    $user->exercises()->attach($exercise->id);}
            }

            return response()->json( $data);
        } else {
            return response()->json([
                'message' => 'Failed to get recommendations from the diet service'
            ], $response->status());
        }
    }
    public function getExercises(){

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        $exercises = $user->exercises->map(function ($exercise) {
            // Convert 'Duration per set' to seconds
            $durationParts = explode(':', $exercise->getAttribute('Duration per set'));
            $durationInSeconds = ($durationParts[0] * 3600) + ($durationParts[1] * 60) + $durationParts[2];

            // Add the converted duration in seconds to the exercise data
            $exercise->duration_per_set_seconds = $durationInSeconds;

            return $exercise;
        });

        return response()->json($exercises, 200);
    }

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

        // Check if the user has already selected this exercise today
        $existingSelection = UserExerciseSelection::where('user_id', $user->id)
            ->where('exercise_id', $exercise->id)
            ->where('date', $today)
            ->first();

        if ($existingSelection) {
            return response()->json(['message' => 'You have already logged this exercise today'], 400);
        }

        // Before saving the exercise selection, reset if needed
        $lastSelectionDate = UserExerciseSelection::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->value('date');

        if ($lastSelectionDate && Carbon::parse($lastSelectionDate)->isBefore($today)) {
            // Reset daily selections
            UserExerciseSelection::where('user_id', $user->id)->delete();
        }

        // Create a new exercise selection record
        $exerciseSelection = new UserExerciseSelection([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'date' => $today,
        ]);
        $exerciseSelection->save();

        // Update the progressions table
        $progression = Progression::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['total_calories' => 0, 'total_fat' => 0, 'total_protein' => 0]
        );

        $progression->decrement('total_calories', $exercise->calories);

        return response()->json(['message' => 'Exercise logged successfully']);
    }
    public function getSelectedExercises()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        $today = Carbon::today()->toDateString();

        /*$selectedExercises = UserExerciseSelection::where('user_id', $user->id)
            ->where('date', $today)
            ->with('exercise') // Include exercise details
            ->get();

        return response()->json([
            'selectedExercises' => $selectedExercises
        ]);*/

        // Get only the meal_ids from the UserMealSelection
        $selectedExerciseIds = UserExerciseSelection::where('user_id', $user->id)
            ->where('date', $today)
            ->pluck('exercise_id');

        return response()->json([
            'selectedExerciseIds' =>   $selectedExerciseIds
        ]);
    }

}
