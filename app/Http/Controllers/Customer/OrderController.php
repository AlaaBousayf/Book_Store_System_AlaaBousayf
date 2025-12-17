<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->with('items.book')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        try {
            return DB::transaction(function () use ($user, $cart) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'payment_method_id' => $cart->payment_method_id,
                    'address' => $cart->address,
                    'total' => 0, // Will calculate below
                    'status' => 'pending'
                ]);

                $total = 0;

                foreach ($cart->items as $item) {
                    // Lock the book row for update to prevent race conditions
                    $book = $item->book()->lockForUpdate()->first();

                    if ($book->stock < $item->qty) {
                        throw new \Exception("Not enough stock for book: {$book->title}");
                    }

                    $price = $book->price;
                    $total += $price * $item->qty;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'book_id' => $item->book_id,
                        'qty' => $item->qty,
                        'price' => $price
                    ]);

                    $book->decrement('stock', $item->qty);
                }

                $order->update(['total' => $total]);

                // Clear the cart
                $cart->items()->delete();
                $cart->delete();

                return (new OrderResource($order->load('items.book')))
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.book'])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['items.book'])
            ->findOrFail($id);

        return new OrderResource($order);
    }
}
