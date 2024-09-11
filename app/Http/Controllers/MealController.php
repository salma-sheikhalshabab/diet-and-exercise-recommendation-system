<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Progression;
use App\Models\UserMealSelection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
    public function get_breakfast_Meals(){
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        $meals = $user->meals()->where('meal_type','breakfast')->get();

        return response()->json($meals);
    }
    public function get_lunch_Meals(){
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $meals = $user->meals()->where('meal_type','lunch')->get();

        return response()->json($meals);
    }
    public function get_dinner_Meals(){
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $meals = $user->meals()->where('meal_type','dinner')->get();

        return response()->json($meals);
    }
    public function get_snack_Meals(){
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }
        $meals = $user->meals()->where('meal_type','snack')->get();

        return response()->json($meals);
    }


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
            // Extract the target weight from the response data
            $target_weight = $data['target_weight'];

            // Update the user's target weight
            $user->target_weight = $target_weight;
            $user->save();
            // Store meals in the database
            foreach ($meals as $mealData) {
                // Find or create the meal in the database
                $meal = Meal::firstOrCreate([
                    'meal_name' => $mealData['name'],
                    'meal_type' => $mealData['meal_type'],
                    'description' => $mealData['description'],
                    'ingredients' => $mealData['ingredients'],
                    'instructions' => $mealData['instructions'],
                    'photo' => $mealData['photo'],
                    'calories' => $mealData['calories'],
                    'fat' => $mealData['fat'],
                    'protein' => $mealData['protein'],

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
   /* public function log_Meal(Request $request)
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
    }*/
    public function log_Meal(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $meal = Meal::find($request->input('meal_id'));
        if (!$meal) {
            return response()->json(['message' => 'Meal not found'], 404);
        }

        $mealType = $meal->meal_type;  // Automatically get the meal type from the Meal model

        $today = Carbon::today()->toDateString();

        // Check if the user has already selected this type of meal today
        $existingSelection = UserMealSelection::where('user_id', $user->id)
            ->where('meal_type', $mealType)
            ->where('date', $today)
            ->first();

        if ($existingSelection) {
            return response()->json(['message' => 'You have already selected a ' . $mealType . ' meal today'], 400);
        }

        // Create a new meal selection record
        $mealSelection = new UserMealSelection([
            'user_id' => $user->id,
            'meal_id' => $meal->id,
            'meal_type' => $mealType,  // Use the meal type retrieved from the Meal model
            'date' => $today,
        ]);
        $mealSelection->save();

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

    public function getSelectedMeals()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized"
            ], 401);
        }

        $today = Carbon::today()->toDateString();

        /*$selectedMeals = UserMealSelection::where('user_id', $user->id)
            ->where('date', $today)
            ->with('meal') // Include meal details
            ->get();

        return response()->json([
            'selectedMeals' => $selectedMeals
        ]);*/
        // Get only the meal_ids from the UserMealSelection
        $selectedMealIds = UserMealSelection::where('user_id', $user->id)
            ->where('date', $today)
            ->pluck('meal_id');

        return response()->json([
            'selectedMealIds' => $selectedMealIds
        ]);
    }

}
