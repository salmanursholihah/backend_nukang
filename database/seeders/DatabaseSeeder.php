<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Service;
use App\Models\SurveyRequest;
use App\Models\TukangProfile;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Chat;
use App\Models\Message;
use App\Models\OrderProgress;
use App\Models\PartnerEarning;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // USERS
        User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@nukang.com',
                'password' => Hash::make('123456'),
                'phone' => '081111111111',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer 1',
                'email' => 'customer@nukang.com',
                'password' => Hash::make('123456'),
                'phone' => '082222222222',
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tukang 1',
                'email' => 'tukang@nukang.com',
                'password' => Hash::make('123456'),
                'phone' => '083333333333',
                'role' => 'tukang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // CATEGORIES
        Category::insert([
            ['name' => 'Bangunan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Listrik', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // SERVICES
        Service::insert([
            [
                'category_id' => 1,
                'name' => 'Pasang Keramik',
                'description' => 'Pasang keramik rumah',
                'price' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => 2,
                'name' => 'Instalasi Lampu',
                'description' => 'Instalasi listrik rumah',
                'price' => 75000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // TUKANG PROFILE
        TukangProfile::create([
            'user_id' => 3,
            'address' => 'Yogyakarta',
            'rating' => 4.8,
            'total_jobs' => 12,
            'is_verified' => true,
        ]);

        // ORDER
        Order::create([
            'customer_id' => 2,
            'tukang_id' => 3,
            'total_price' => 150000,
            'service_date' => now(),
            'address' => 'Jl Malioboro',
            'status' => 'pending',
        ]);

        // ORDER DETAIL
        OrderDetail::create([
            'order_id' => 1,
            'service_id' => 1,
            'price' => 50000,
            'qty' => 3,
        ]);

        // REVIEW
        Review::create([
            'order_id' => 1,
            'customer_id' => 2,
            'tukang_id' => 3,
            'rating' => 5,
            'comment' => 'Bagus sekali',
        ]);

        // CHAT
        Chat::create([
            'customer_id' => 2,
            'tukang_id' => 3,
        ]);

        // MESSAGE
        Message::create([
            'chat_id' => 1,
            'sender_id' => 2,
            'message' => 'Halo pak tukang',
            'is_read' => false,
        ]);

        // PARTNER EARNING
        PartnerEarning::create([
            'tukang_id' => 3,
            'order_id' => 1,
            'amount' => 120000,
            'status' => 'pending',
        ]);

        // ORDER PROGRESS
        OrderProgress::create([
            'order_id' => 1,
            'title' => 'Pengerjaan dimulai',
            'description' => 'Hari pertama pengerjaan',
        ]);

        // SURVEY REQUEST
        SurveyRequest::create([
            'customer_id' => 2,
            'tukang_id' => 3,
            'service_id' => 1,
            'address' => 'Jl Solo',
            'survey_date' => now(),
            'survey_fee' => 50000,
            'estimated_price' => 300000,
            'estimated_days' => 3,
            'notes' => 'Survey awal rumah',
            'status' => 'requested',
        ]);
    }
}
