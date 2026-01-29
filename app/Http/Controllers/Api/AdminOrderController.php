<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    // Fetch all orders with pagination
    public function index()
    {
        $orders = Order::with('materials') // Eager load materials
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    // Fetch a single order details
    public function show($id)
    {
        $order = Order::with('materials')->findOrFail($id);
        return response()->json($order);
    }

    // Update order status
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,pending,paid,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated', 'order' => $order]);
    }
}
