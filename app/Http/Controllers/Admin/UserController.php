<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('type', '!=', 'admin')->latest()->get();
        return UserResource::collection($users);
    }

    public function block(User $user)
    {
        if ($user->type === 'admin') {
            return response()->json(['message' => 'Cannot block an admin'], 403);
        }

        $user->block();

        return response()->json([
            'message' => 'User blocked successfully',
            'user' => new UserResource($user)
        ]);
    }
}
