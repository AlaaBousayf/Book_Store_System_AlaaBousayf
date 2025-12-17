<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return PaymentMethodResource::collection(PaymentMethod::all());
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:payment_methods,name'],
        ]);

        $paymentMethod = PaymentMethod::create($validated);

        return response()->json([
            'message' => 'Payment method created successfully',
            'payment_method' => new PaymentMethodResource($paymentMethod)
        ], 201);
    }

    public function show(string $id)
    {
        return new PaymentMethodResource(PaymentMethod::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:payment_methods,name,' . $id],
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'message' => 'Payment method updated successfully',
            'payment_method' => new PaymentMethodResource($paymentMethod)
        ]);
    }

    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();

        return response()->json([
            'message' => 'Payment method deleted successfully'
        ]);
    }
}
