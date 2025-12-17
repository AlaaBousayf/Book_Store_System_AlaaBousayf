<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        return new CartResource($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $book_id)
    {
        $user = Auth::user();

        // if user does not have a cart
        $cart = Cart::where('user_id', $user->id)->firstOrCreate([
            'user_id' => $user->id,
            'payment_method_id' => PaymentMethod::first()->id,
            'address' => $user->customer->address
        ]);

        $cartItem = CartItem::where('cart_id', $cart->id)->where('book_id', $book_id)->first();

        if ($cartItem) {
            $cartItem->update([
                'qty' => $cartItem->qty + 1
            ]);
        } else {
            // add item to the cart
            $cart->items()->create([
                'book_id' => $book_id,
                'qty' => 1
            ]);
        }

        return response()->json([
            'message' => 'item added'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cart = Cart::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'payment_method_id' => ['sometimes', 'required', 'exists:payment_methods,id'],
            'address' => ['sometimes', 'required', 'string']
        ]);

        $cart->update($validated);

        return new CartResource($cart);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($book_id)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('book_id', $book_id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'message' => 'item removed'
        ]);
    }
}
