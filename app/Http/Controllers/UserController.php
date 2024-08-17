<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request){

        $validator = Validator::make($request->all(),[
            'name'=>'string',
            'age'=>'date',
            'height'=>'string',
            'weight'=>'string',
            'gender'=>'string',
            'activity_level'=>'string',
            'allergy' => [

                'array',
                Rule::in(['Peanut', 'Tree nuts', 'Milk', 'Egg','Fish','Sesame','Soybean','Wheat','shellfish']),
            ],
            'disease' => [

                'array',
                Rule::in(['heart disease','diabetes','hypertension']),
            ],

        ]);
        if($validator->fails())
        { return response()->json(["validation_errors:"=>$validator->errors()],422);};
        $name=$request->name;
        $age=$request->age;
        $height=$request->height;
        $weight=$request->weight;
        $gender=$request->gender;
        $activity_level=$request->activity_level;
        $disease=$request->disease;
        $allergy=$request->allergy;

        $user=User::find(auth()->id());
        if(!$user){
            return response()->json(["success"=>"0",
                "message"=>"invalid id"],401);
        }
        if($name){
            $user->update(['name'=>$name]);
        }



        if ($age){
            $user->update([
                'age' => $age,
            ]);
        }

        if ($height){
            $user->update([
                'height' => $height,
            ]);
        }
        if ($weight){
            $user->update([
                'weight' => $weight,
            ]);
        }
        if ($gender){
            $user->update([
                'gender' => $gender,
            ]);
        }
        if ($activity_level){
            $user->update([
                'activity_level' => $activity_level,
            ]);
        }
        if ($disease){
            $user->update([
                'disease' => $disease,
            ]);
        }
        if ($allergy){
            $user->update([
                'allergy' => $allergy,
            ]);
        }

        return response()->json(["message"=>"user has been updated successfully!",$user],200);
    }
    public function delete(){

        $id=auth()->id();
        $user=DB::table('users')->where('id',$id)->delete();
        if(!$user){
            return response()->json([
                "success"=>"0",
                "message"=>"invalid id"],404);
        }
        return response()->json(["success"=>"1",
            "message"=>"user has been deleted successfully!"],200);
    }
}
