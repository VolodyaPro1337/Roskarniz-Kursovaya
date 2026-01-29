<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'category', 'image', 'description', 'properties'
    ];

    protected $casts = [
        'properties' => 'array',
        'price' => 'decimal:2',
    ];
}
