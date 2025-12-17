<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $inputs = $request->validate([

            'name'=>['required'],
            'username'=>['required','unique:users'],
            'password'=>['required'],
            'bio'=>['required'],
            'country'=>['required']
        ]);
        $inputs['type'] = 'author';
        $inputs['status'] = 'pending';
        $user = User::create($inputs);
        $user->author()->create($inputs);

        return response()->json([
            'message'=>'you were signedup wait for approve'
        ],201);



    }
}
