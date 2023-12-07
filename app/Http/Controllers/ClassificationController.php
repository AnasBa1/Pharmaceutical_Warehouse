<?php

namespace App\Http\Controllers;

use App\Models\MedicalClassification;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    public function listAllClassifications(): JsonResponse
    {
        $classifications = MedicalClassification::query()
            ->select('medical_classifications.id', 'medical_classifications.classification')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The classifications list has been successfully retrieved.',
            'data' => $classifications
        ]);
    }

    public function listMedicationsClassification($id): JsonResponse
    {
        $medications = Medication::query()->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('medications.medical_classification_id', '=', $id)
            ->select('medications.id', 'medications.trade_name', 'medical_classifications.classification', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The medications list has been successfully retrieved.',
            'data' => $medications
        ]);
    }
}
