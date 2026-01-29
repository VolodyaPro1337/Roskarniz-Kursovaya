<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by Opacity (JSON)
        if ($request->filled('opacity') && is_array($request->opacity)) {
            foreach ($request->opacity as $opacity) {
                 $query->whereJsonContains('properties->opacity', $opacity);
            }
        }

        // Filter by Room (JSON)
        if ($request->filled('room') && is_array($request->room)) {
             $query->where(function($q) use ($request) {
                foreach ($request->room as $room) {
                    $q->orWhereJsonContains('properties->room', $room);
                }
             });
        }

        // Filter by Color (JSON)
        // Since properties->color is a single string in seeder, checking if it is IN request array
        if ($request->filled('color') && is_array($request->color)) {
            // Simplified JSON check for properties->color being in list of values
            $query->whereIn('properties->color', $request->color);
            // Note: If properties->color was an array, we'd need whereJsonContains. 
            // Since it is a string value in JSON, strict SQL JSON operators might be needed for whereIn on path.
            // Safe fallback for SQLite/MySQL:
            // $query->where(function($q) use ($request) {
            //    foreach ($request->color as $c) $q->orWhere('properties->color', $c);
            // });
        }

        return response()->json($query->get());
    }
}
