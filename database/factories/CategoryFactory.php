<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Instalasi Listrik',
            'Perbaikan AC',
            'Renovasi Rumah',
            'Pengecatan',
            'Perbaikan Atap',
            'Instalasi CCTV',
            'Perbaikan Pipa',
            'Tukang Bangunan',
            'Interior Design',
            'Taman & Landscaping',
        ]);

        return [
            'name'      => $name,
            'slug'      => Str::slug($name),
            'icon'      => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
