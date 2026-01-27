<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $styles = [
            ['id' => 'minimal', 'name' => 'Минимализм', 'image' => '/images/atelier/style_minimal.png'],
            ['id' => 'classic', 'name' => 'Классика', 'image' => '/images/atelier/style_classic.png'],
            ['id' => 'loft', 'name' => 'Лофт', 'image' => '/images/atelier/style_loft.png'],
            ['id' => 'scandi', 'name' => 'Сканди', 'image' => '/images/atelier/style_scandi.png']
        ];

        foreach ($styles as $s) {
            \App\Models\Material::firstOrCreate(
                ['slug' => $s['id']],
                [
                    'category' => 'style',
                    'name' => $s['name'],
                    'image_url' => $s['image'],
                    'price' => 0
                ]
            );
        }

        $fabrics = [
            ['id' => 'velvet', 'name' => 'Бархат Royal', 'desc' => 'Плотный ворс, глубокий отлив', 'gradient' => 'linear-gradient(135deg, #372828 0%, #684e4e 100%)'],
            ['id' => 'linen', 'name' => 'Эко-Лен', 'desc' => 'Грубая фактура, натуральность', 'gradient' => 'linear-gradient(135deg, #e3dacd 0%, #b8ad9e 100%)'],
            ['id' => 'silk', 'name' => 'Дикий Шелк', 'desc' => 'Мягкий блеск, струящаяся ткань', 'gradient' => 'linear-gradient(135deg, #f5f5f5 0%, #dcdcdc 100%)'],
            ['id' => 'blackout', 'name' => 'Dimout 90%', 'desc' => 'Защита от солнца и шума', 'gradient' => 'linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%)']
        ];

        foreach ($fabrics as $f) {
            \App\Models\Material::firstOrCreate(
                ['slug' => $f['id']],
                [
                    'category' => 'fabric',
                    'name' => $f['name'],
                    'description' => $f['desc'],
                    'price' => 5000,
                    'properties' => ['gradient' => $f['gradient']]
                ]
            );
        }

        $colors = [
            ['id' => 'warm_beige', 'name' => 'Песок', 'hex' => '#d6cdb8'],
            ['id' => 'cold_grey', 'name' => 'Графит', 'hex' => '#374151'],
            ['id' => 'accent_ruby', 'name' => 'Рубин', 'hex' => '#9f1239'],
            ['id' => 'base_white', 'name' => 'Молочный', 'hex' => '#fafafa']
        ];

        foreach ($colors as $c) {
            \App\Models\Material::firstOrCreate(
                ['slug' => $c['id']],
                [
                    'category' => 'color',
                    'name' => $c['name'],
                    'price' => 0,
                    'properties' => ['hex' => $c['hex']]
                ]
            );
        }

        $cornices = [
            ['id' => 'profile', 'name' => 'Профильный (Скрытый)', 'desc' => 'Минимализм, монтаж в нишу', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
            ['id' => 'decorative', 'name' => 'Декоративный (Труба)', 'desc' => 'Акцент на фурнитуру', 'icon' => 'M2 12h20M2 12a2 2 0 012-2h16a2 2 0 012 2v0a2 2 0 01-2 2H4a2 2 0 01-2-2v0z'],
            ['id' => 'electro', 'name' => 'Электрокарниз', 'desc' => 'Управление с пульта/Умного дома', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z']
        ];

        foreach ($cornices as $co) {
            \App\Models\Material::firstOrCreate(
                ['slug' => $co['id']],
                [
                    'category' => 'cornice',
                    'name' => $co['name'],
                    'description' => $co['desc'],
                    'price' => 15000,
                    'properties' => ['icon' => $co['icon']]
                ]
            );
        }
    }
}
