<?php

namespace App\Http\Controllers;

use App\Models\MedicalClassification;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make(['id' => $id], [
            'id' => ['exists:medical_classifications,id'],
        ],
            [
                'id.exists' => 'The selected classification does not exists.'
            ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::user()->role == 'manager') {
            $medications = Medication::query()->withTrashed()
                ->where('medications.medical_classification_id', '=', $id)
                ->select('medications.id', 'medications.trade_name', 'medications.available_quantity', 'medications.price')
                ->get();
        } else {
            $medications = Medication::query()
                ->where('medications.medical_classification_id', '=', $id)
                ->select('medications.id', 'medications.trade_name', 'medications.available_quantity', 'medications.price')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'The medications list has been successfully retrieved.',
            'data' => $medications
        ]);
    }
}
