<?php


namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Instalasi Listrik',
            'Perbaikan AC',
            'Pengecatan',
            'Perbaikan Pipa',
            'Renovasi Bangunan',
        ];

        foreach ($categories as $name) {
            Category::create([
                'name'      => $name,
                'slug'      => Str::slug($name),
                'is_active' => true,
            ]);
        }
    }
}
