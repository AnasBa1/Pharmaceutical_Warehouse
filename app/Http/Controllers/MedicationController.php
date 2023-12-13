<?php

namespace App\Http\Controllers;

use App\Models\MedicalClassification;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicationController extends Controller
{
    public function listValidMedications(): JsonResponse
    {
        $medications = Medication::query()->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
                                  ->select('medications.id', 'medications.trade_name', 'medical_classifications.classification', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
                                  ->get();

        return response()->json([
            'status' => true,
            'message' => 'The medications list has been successfully retrieved.',
            'data' => $medications
        ]);
    }

    public function listExpiredMedications(): JsonResponse
    {
        $medications = Medication::query()->onlyTrashed()->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->select('medications.id', 'medications.trade_name', 'medical_classifications.classification', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The medications list has been successfully retrieved.',
            'data' => $medications
        ]);
    }

    public function createMedication(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scientific_name' => ['nullable'],
            'trade_name' => ['required'],
            'medical_classification_id' => ['required', 'exists:medical_classifications,id'],
            'manufacturer' => ['nullable'],
            'available_quantity' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['required', 'date'],
            'price' => ['required', 'integer', 'min:0']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $medication = Medication::query()->create([
            'scientific_name' => $request['scientific_name'],
            'trade_name' => $request['trade_name'],
            'medical_classification_id' => $request['medical_classification_id'],
            'manufacturer' => $request['manufacturer'],
            'available_quantity' => $request['available_quantity'],
            'expiration_date' => $request['expiration_date'],
            'price' => $request['price'],
        ]);

        return response()->json($this->showMedication($medication['id'])->original, 201);
    }

   public function search(Request $request): JsonResponse
    {
        $search = $request['search'];
        $medications = Medication::query()->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('medications.trade_name', 'LIKE', "%$search%")
            ->orWhere('medications.scientific_name', 'LIKE', "%$search%")
            ->get(['medications.id','medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.available_quantity', 'medications.expiration_date', 'medications.price']);

        $classifications = MedicalClassification::query()->where('medical_classifications.classification', 'LIKE', "%$search%")
        ->get(['medical_classifications.id', 'medical_classifications.classification']);

        return response()->json([
            'status' => true,
            'message' => 'The medications and classifications has been found successfully.',
            'data' => [
                'medications' => $medications,
                'classifications' => $classifications
            ]]);
    }

    public function showMedication($id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['exists:medications,id'],
        ],
        [
            'id.exists' => 'The selected medication does not exists.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $medication = Medication::query()->withTrashed()
            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('medications.id', '=', $id)
            ->first(['medications.id','medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price']);

        return response()->json([
            'status' => true,
            'message' => 'The medication has been found successfully.',
            'data' => [
                'medication' => $medication
            ]
        ]);
    }
}
