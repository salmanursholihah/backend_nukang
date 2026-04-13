<?php

namespace Database\Seeders;

use Database\Seeders\CategorySeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ServiceSeeder::class,
            TukangProfileSeeder::class,
            OrderSeeder::class,
            SurveyRequestSeeder::class,
            ChatSeeder::class,
            EarningSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
