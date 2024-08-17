<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Meal;

class DietController extends Controller
{
    public function getDietRecommendation()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        // Calculate the user's age from their birthdate
        $user_birthdate = $user->age;
        $user_age = Carbon::parse($user_birthdate)->age; // Convert the date to age in years

        // Prepare the user information to be sent to the Flask server
        $userInfo = [
            'age'  => $user_age,
            'gender' => $user->gender,
            'height' => $user->height,
            'weight' => $user->weight,
            'allergy' => $user->allergy,
            'disease' => $user->disease,
            'activity_level' => $user->activity_level,
        ];

        // Send the user information to the Flask server
        $response = Http::post('http://127.0.0.1:5000/diet-recommendation', $userInfo);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();
            $meals = $data['meals'];

            // Store meals in the database
            foreach ($meals as $mealData) {
                // Find or create the meal in the database
                $meal = Meal::firstOrCreate([
                    'meal_name' => $mealData['name'],
                    'meal_type' => $mealData['meal_type'],
                    'description' => $mealData['description'],
                    'photo' => $mealData['photo'],
                    'calories' => $mealData['calories'],
                    'fat' => $mealData['fat'],
                    'protein' => $mealData['protein'],
                    //'carbs' => $mealData['carbs'],
                ]);

                // Check if the user already has this meal
                if (!$user->meals()->where('meal_id', $meal->id)->exists()) {
                    $user->meals()->attach($meal->id);
                }
            }

            return response()->json($data);
        } else {
            return response()->json([
                'message' => 'Failed to get recommendations from the diet service'
            ], $response->status());
        }
    }
}
