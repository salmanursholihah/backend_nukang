<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            'Pasang Stop Kontak'    => ['unit' => 'titik',  'base' => 75000,  'per_unit' => 50000],
            'Ganti MCB / Sekring'   => ['unit' => 'unit',   'base' => 100000, 'per_unit' => 75000],
            'Servis AC 1/2 PK'      => ['unit' => 'unit',   'base' => 150000, 'per_unit' => 125000],
            'Isi Freon AC'          => ['unit' => 'unit',   'base' => 200000, 'per_unit' => 175000],
            'Cat Dinding Interior'  => ['unit' => 'meter',  'base' => 50000,  'per_unit' => 35000],
            'Cat Dinding Eksterior' => ['unit' => 'meter',  'base' => 65000,  'per_unit' => 45000],
            'Pasang Keramik'        => ['unit' => 'meter',  'base' => 120000, 'per_unit' => 85000],
            'Perbaikan Atap Bocor'  => ['unit' => 'titik',  'base' => 200000, 'per_unit' => 150000],
            'Pasang Kamera CCTV'    => ['unit' => 'unit',   'base' => 350000, 'per_unit' => 250000],
            'Perbaikan Pipa Bocor'  => ['unit' => 'titik',  'base' => 150000, 'per_unit' => 100000],
        ];

        $name    = fake()->unique()->randomElement(array_keys($services));
        $detail  = $services[$name];

        return [
            'category_id'    => Category::factory(),
            'name'           => $name,
            'slug'           => Str::slug($name),
            'description'    => fake()->paragraph(2),
            'base_price'     => $detail['base'],
            'price_per_unit' => $detail['per_unit'],
            'unit'           => $detail['unit'],
            'thumbnail'      => null,
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
