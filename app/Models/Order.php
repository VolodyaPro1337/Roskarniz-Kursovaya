<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'guest_info' => 'array',
    ];

    public function materials()
    {
        return $this->belongsToMany(Material::class);
    }
}
