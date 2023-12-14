<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Under Preparing ',
            'Shipped',
            'Received',
            'Refused',
        ];

        foreach ($statuses as $status){
            OrderStatus::query()->create([
                'status' => $status,
            ]);
        }
    }
}
