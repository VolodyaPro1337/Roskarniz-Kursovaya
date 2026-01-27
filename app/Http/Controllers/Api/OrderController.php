<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'materials' => 'required|array',
            'materials.*' => 'exists:materials,id',
            'guest_info' => 'nullable|array',
            'total_price' => 'required|numeric'
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $request->user()?->id,
            'guest_info' => $validated['guest_info'] ?? null,
            'total_price' => $validated['total_price'],
            'status' => 'new'
        ]);

        $order->materials()->attach($validated['materials']);

        return response()->json(['message' => 'Order created', 'order_id' => $order->id], 201);
    }
}
