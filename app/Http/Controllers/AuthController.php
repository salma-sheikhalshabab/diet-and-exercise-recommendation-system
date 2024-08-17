<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function logInUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }
        $credentials = request(['email', 'password']);
        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => "check your credentials"
            ], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('personal access token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'message' => "user logged in successfully",
            'token' => $token,
            'user' => $user
        ]);
    }

    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'age' => 'required|date',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'gender' => 'required',
            'activity_level' => 'required',
            'allergy' => [
                'nullable',
                'array',
                Rule::in(['Peanut', 'Tree nuts', 'Milk', 'Egg','Fish','Sesame','Soybean','Wheat','shellfish']),
            ],
            'disease' => [
                'nullable',
                'array',
                Rule::in(['heart disease','diabetes','hypertension']),
            ],
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 401);
        }

        $user = new User();
        $user->name = $request->name;
        $user->age = $request->age;
        $user->height = $request->height;
        $user->weight = $request->weight;
        $user->gender = $request->gender;
        $user->activity_level = $request->activity_level;
        $user->disease = $request->disease;
        $user->allergy = $request->allergy;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();


        $tokenResult = $user->createToken('personal access token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'message' => "user registered successfully",
            'token' => $token,
            'user' => $user
        ]);

    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function logOut(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => "user logged out"]);
    }
}
