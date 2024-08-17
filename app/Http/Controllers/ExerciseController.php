<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
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
                    //'gif' => $exerciseData['gif'],
                   // 'exercise_image' => $exerciseData['exercise_image'],
                    'calories' => $exerciseData['calories_burnt'],
                    'duration' => $exerciseData['duration'],

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


        $exercises = $user->exercises;

        return response()->json( $exercises,200);
    }
}
