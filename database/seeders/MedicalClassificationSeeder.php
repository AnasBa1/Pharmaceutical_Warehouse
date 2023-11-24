<?php

namespace Database\Seeders;

use App\Models\MedicalClassification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicalClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            'Headache',
            'Stomachache',
            'Allergy'
        ];

        foreach ($classifications as $classification){
            MedicalClassification::query()->create([
                'classification' => $classification,
            ]);
        }
    }
}
