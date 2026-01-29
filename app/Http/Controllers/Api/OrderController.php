<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'materials' => 'required|array',
            'materials.*' => 'exists:materials,slug', // Валидация по slug
            'guest_info' => 'nullable|array',
            'guest_info.name' => 'nullable|string|max:255',
            'guest_info.phone' => 'nullable|string|max:50',
            'guest_info.email' => 'nullable|email|max:255',
            'total_price' => 'required|numeric|min:0',
            'complexity' => 'nullable|string',
            'estimated_days' => 'nullable|integer'
        ]);

        $order = Order::create([
            'user_id' => $request->user()?->id,
            'guest_info' => $validated['guest_info'] ?? null,
            'total_price' => $validated['total_price'],
            'status' => 'new'
        ]);

        // Получаем реальные ID материалов по slug
        $materialIds = Material::whereIn('slug', $validated['materials'])->pluck('id');
        $order->materials()->attach($materialIds);

        return response()->json([
            'success' => true,
            'message' => 'Заказ создан',
            'order_id' => $order->id
        ], 201);
    }
}
