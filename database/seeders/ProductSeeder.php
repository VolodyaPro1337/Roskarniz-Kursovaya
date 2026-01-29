<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Moonlight Silence',
                'slug' => 'moonlight-silence',
                'price' => 15900,
                'category' => 'curtains',
                'image' => '/images/product-1.jpg',
                'properties' => ['opacity' => 'blackout', 'room' => 'bedroom', 'color' => 'grey']
            ],
            [
                'name' => 'Morning Breeze',
                'slug' => 'morning-breeze',
                'price' => 8500,
                'category' => 'tulle',
                'image' => '/images/product-2.jpg',
                'properties' => ['opacity' => 'sheer', 'room' => 'living', 'color' => 'white']
            ],
            [
                'name' => 'Somfy Glydea Ultra',
                'slug' => 'somfy-glydea-ultra',
                'price' => 28000,
                'category' => 'electro',
                'image' => '/images/product-motor.jpg',
                'properties' => ['opacity' => 'n/a', 'room' => 'living', 'color' => 'white']
            ],
            [
                'name' => 'Velvet Touch',
                'slug' => 'velvet-touch',
                'price' => 22000,
                'category' => 'curtains',
                'image' => '/images/product-3.jpg',
                'properties' => ['opacity' => 'dimout', 'room' => 'living', 'color' => 'beige']
            ],
            [
                'name' => 'Linen Eco',
                'slug' => 'linen-eco',
                'price' => 12400,
                'category' => 'curtains',
                'image' => '/images/product-4.jpg',
                'properties' => ['opacity' => 'light', 'room' => 'kitchen', 'color' => 'beige']
            ],
            [
                'name' => 'Kids Dream',
                'slug' => 'kids-dream',
                'price' => 9900,
                'category' => 'curtains',
                'image' => '/images/product-1.jpg',
                'properties' => ['opacity' => 'dimout', 'room' => 'kids', 'color' => 'blue']
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::firstOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
}
