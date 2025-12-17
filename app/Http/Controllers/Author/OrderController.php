<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Resources\Author\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $authorBookIds = $user->books()->pluck('books.id');

        $orders = Order::whereHas('items', function ($query) use ($authorBookIds) {
            $query->whereIn('book_id', $authorBookIds);
        })->with(['items' => function ($query) use ($authorBookIds) {
            $query->whereIn('book_id', $authorBookIds)->with('book');
        }, 'user'])->latest()->get();

        return OrderResource::collection($orders);
    }
}
