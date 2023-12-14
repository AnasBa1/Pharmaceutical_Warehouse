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
            'Analgesic',
            'Allergy',
            'Musculoskeletal System',
            'Gastro-intestinal',
            'Respiratory Tract',
            'Dermatological',
            'Anti-infectives',
            'Ophthalmic',
            'Oral And Dental Conditions',
            'Vitamins',
            'Neurological',
            'Cardiovascular',
            'Ear, Nose And Throat',
        ];

        foreach ($classifications as $classification){
            MedicalClassification::query()->create([
                'classification' => $classification,
            ]);
        }
    }
}
