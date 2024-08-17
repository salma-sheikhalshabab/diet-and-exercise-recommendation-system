<?php

use App\Http\Controllers\DietController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\ProgressionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'user'],function(){
    Route::post('/login',[AuthController::class,'logInUser']);
    Route::post('/register',[AuthController::class,'registerUser']);

    });

Route::group(['prefix'=>'user','middleware'=>'auth:sanctum'],function(){

    Route::get('/profile',[AuthController::class,'profile']);
    Route::get('/logout',[AuthController::class,'logOut']);
    Route::put('/update',[UserController::class,'update']);
    Route::delete('/delete',[UserController::class,'delete']);

    Route::post('/diet-recommendation', [DietController::class,'getDietRecommendation']);
    Route::get('/meals',[MealController::class,'getMeals']);

    Route::post('/exercise-recommendation', [ExerciseController::class,'getExerciseRecommendation']);
    Route::get('/exercises',[ExerciseController::class,'getExercises']);

});

Route::group(['prefix'=>'progress','middleware'=>'auth:sanctum'],function(){

    Route::post('/log-meal', [ProgressionController::class, 'log_Meal']);
    Route::post('/log-exercise', [ProgressionController::class, 'log_exercise']);

    Route::get('/daily', [ProgressionController::class, 'get_Daily_Progress']);
    Route::get('/weekly', [ProgressionController::class, 'get_Weekly_Progress']);
    Route::get('/monthly', [ProgressionController::class, 'get_Monthly_Progress']);
});
