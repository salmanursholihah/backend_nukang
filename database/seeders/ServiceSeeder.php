<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Instalasi Listrik' => [
                ['name' => 'Pasang Stop Kontak',   'base' => 75000,  'unit' => 'titik'],
                ['name' => 'Ganti MCB / Sekring',  'base' => 100000, 'unit' => 'unit'],
                ['name' => 'Pasang Saklar Lampu',  'base' => 50000,  'unit' => 'titik'],
                ['name' => 'Instalasi Kabel Baru', 'base' => 200000, 'unit' => 'meter'],
                ['name' => 'Perbaikan Korsleting', 'base' => 250000, 'unit' => 'titik'],
            ],
            'Perbaikan AC' => [
                ['name' => 'Servis AC 1/2 PK',     'base' => 150000, 'unit' => 'unit'],
                ['name' => 'Servis AC 1 PK',        'base' => 175000, 'unit' => 'unit'],
                ['name' => 'Isi Freon AC',           'base' => 200000, 'unit' => 'unit'],
                ['name' => 'Bongkar Pasang AC',      'base' => 350000, 'unit' => 'unit'],
                ['name' => 'Perbaikan AC Mati Total', 'base' => 300000, 'unit' => 'unit'],
            ],
            'Pengecatan' => [
                ['name' => 'Cat Dinding Interior',   'base' => 50000,  'unit' => 'meter'],
                ['name' => 'Cat Dinding Eksterior',  'base' => 65000,  'unit' => 'meter'],
                ['name' => 'Cat Plafon',             'base' => 55000,  'unit' => 'meter'],
                ['name' => 'Cat Pagar Besi',         'base' => 45000,  'unit' => 'meter'],
                ['name' => 'Cat Tembok Full Rumah',  'base' => 3000000, 'unit' => 'unit'],
            ],
            'Perbaikan Pipa' => [
                ['name' => 'Perbaikan Pipa Bocor',   'base' => 150000, 'unit' => 'titik'],
                ['name' => 'Pasang Pipa Baru',       'base' => 100000, 'unit' => 'meter'],
                ['name' => 'Ganti Kran Air',         'base' => 75000,  'unit' => 'unit'],
                ['name' => 'Bersihkan Saluran Mampet', 'base' => 200000, 'unit' => 'titik'],
                ['name' => 'Pasang Shower',          'base' => 250000, 'unit' => 'unit'],
            ],
            'Renovasi Bangunan' => [
                ['name' => 'Pasang Keramik Lantai',  'base' => 120000, 'unit' => 'meter'],
                ['name' => 'Pasang Keramik Dinding', 'base' => 130000, 'unit' => 'meter'],
                ['name' => 'Pasang Plafon Gypsum',   'base' => 100000, 'unit' => 'meter'],
                ['name' => 'Perbaikan Atap Bocor',   'base' => 200000, 'unit' => 'titik'],
                ['name' => 'Bangun Dinding Bata',    'base' => 250000, 'unit' => 'meter'],
            ],
        ];

        foreach ($data as $catName => $services) {
            $category = Category::where('name', $catName)->first();
            if (! $category) continue;

            foreach ($services as $s) {
                Service::create([
                    'category_id'    => $category->id,
                    'name'           => $s['name'],
                    'slug'           => Str::slug($s['name']),
                    'base_price'     => $s['base'],
                    'price_per_unit' => $s['base'] * 0.8,
                    'unit'           => $s['unit'],
                    'is_active'      => true,
                ]);
            }
        }
    }
}
