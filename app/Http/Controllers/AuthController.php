<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $inputs = $request->validate([
            'username'=>['required','string','min:4','max:250'],
            'password'=>['required','min:8']
        ]);




        $user = User::where('username',$inputs['username'])->first();

        if(!$user || !Hash::check($inputs['password'],$user->password)){
            return response()->json([
                'message'=>'wrong username or password'
            ],401);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'access_token'=>$token,
            'type'=>'Bearer',
            'user' => new UserResource($user)
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user)
        ]);
    }
}
