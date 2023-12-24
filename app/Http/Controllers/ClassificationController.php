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
                ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
                ->where('medications.medical_classification_id', '=', $id)
                ->select('medications.id', 'medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
                ->get();
        } else {
            $medications = Medication::query()
                ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
                ->where('medications.medical_classification_id', '=', $id)
                ->select('medications.id', 'medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'The medications list has been successfully retrieved.',
            'data' => $medications
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $search = $request['search'];

        $classifications = MedicalClassification::query()->where('medical_classifications.classification', 'LIKE', "%$search%")
            ->get(['medical_classifications.id', 'medical_classifications.classification']);

        return response()->json([
            'status' => true,
            'message' => 'The classifications has been found successfully.',
            'data' => $classifications
        ]);
    }
}
