<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = \App\Models\Material::all()->groupBy('category');
        
        return response()->json([
            'styles' => $materials->get('style', collect())->map(fn($i) => [
                'id' => $i->slug,
                'name' => $i->name,
                'image' => $i->image_url
            ]),
            'fabrics' => $materials->get('fabric', collect())->map(fn($i) => [
                'id' => $i->slug,
                'name' => $i->name,
                'desc' => $i->description,
                'gradient' => $i->properties['gradient'] ?? ''
            ]),
            'colors' => $materials->get('color', collect())->map(fn($i) => [
                'id' => $i->slug,
                'name' => $i->name,
                'hex' => $i->properties['hex'] ?? ''
            ]),
            'cornices' => $materials->get('cornice', collect())->map(fn($i) => [
                'id' => $i->slug,
                'name' => $i->name,
                'desc' => $i->description,
                'icon' => $i->properties['icon'] ?? ''
            ]),
        ]);
    }
}
