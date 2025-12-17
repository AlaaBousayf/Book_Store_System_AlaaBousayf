<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $inputs = $request->validate([
            //user
            'name'=>['required'],
            'username'=>['required','unique:users'],
            'password'=>['required'],
            // customer
            'phone_number'=>['required'],
            'address'=>['required'],
            'email'=>['required','email']
        ]);
        $inputs['type'] = 'customer';

        //TODO:: make db transaction
        $user = User::create($inputs);
        $user->customer()->create($inputs);

        return response()->json([
            'message'=>'you signed up '
        ],201);



    }
}
