<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function favoritesList(): JsonResponse
    {
        $userId = Auth::user()->getAuthIdentifier();

        $favorites = Favorite::query()
            ->where('user_id', '=', $userId)
            ->join('medications', 'medication_id', '=', 'medications.id')
            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->select('medications.id', 'medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
            ->selectRaw('true as "favorite"')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'The favorites list has been successfully retrieved.',
            'data' => $favorites,
        ]);
    }

    public function addToFavorites($id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['exists:medications,id',
                Rule::unique('favorites', 'medication_id')->where(function ($query) {
                return $query->where('user_id', Auth::user()->getAuthIdentifier());
            }),],
        ],
        [
            'id.exists' => 'The selected medication does not exists.',
            'id.unique' => 'The medication is already in your favorites.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::user()->getAuthIdentifier();

        Favorite::query()->create([
            'user_id' => $userId,
            'medication_id' => $id,
        ]);

        $favorites = $this->favoritesList()->original['data'];

        return response()->json([
            'status' => true,
            'message' => 'The medication has been added to favorites successfully.',
            'data' => $favorites,
        ], 201);
    }

    public function removeFromFavorites($id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => [Rule::exists('favorites', 'medication_id')->where(function ($query) {
                    return $query->where('user_id', Auth::user()->getAuthIdentifier());
                }),],
        ],
        [
            'id.exists' => 'The medication does not exists in your favorites.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::user()->getAuthIdentifier();

        Favorite::query()
            ->where('user_id', '=', $userId)
            ->where('medication_id', '=', $id)
            ->delete();

        $favorites = $this->favoritesList()->original['data'];

        return response()->json([
            'status' => true,
            'message' => 'The medication has been removed from your favorites.',
            'data' => $favorites,
        ]);
    }

    public function listValidMedications(): JsonResponse
    {
        $medications = Medication::query()->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->select('medications.id', 'medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
            ->get();

        $favoriteMedicationIds = $this->favoritesList()->original['data']->pluck('id')->toArray();
        foreach ($medications as $medication) {
            if (in_array($medication['id'], $favoriteMedicationIds))
                $medication['favorite'] = 1;
            else $medication['favorite'] = 0;
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
        $medications = Medication::query()
            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('medications.trade_name', 'LIKE', "%$search%")
            ->orWhere('medications.scientific_name', 'LIKE', "%$search%")
            ->select('medications.id', 'medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
            ->get();

        $favoriteMedicationIds = $this->favoritesList()->original['data']->pluck('id')->toArray();
        foreach ($medications as $medication) {
            if (in_array($medication['id'], $favoriteMedicationIds))
                $medication['favorite'] = 1;
            else $medication['favorite'] = 0;
        }

        return response()->json([
            'status' => true,
            'message' => 'The medications has been found successfully.',
            'data' => $medications
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

        $medications = Medication::query()
            ->join('medical_classifications', 'medical_classification_id', '=', 'medical_classifications.id')
            ->where('medications.medical_classification_id', '=', $id)
            ->select('medications.id', 'medications.scientific_name' , 'medications.trade_name', 'medical_classifications.classification', 'medications.manufacturer', 'medications.available_quantity', 'medications.expiration_date', 'medications.price')
            ->get();

        $favoriteMedicationIds = $this->favoritesList()->original['data']->pluck('id')->toArray();
        foreach ($medications as $medication) {
            if (in_array($medication['id'], $favoriteMedicationIds))
                $medication['favorite'] = 1;
            else $medication['favorite'] = 0;
        }

        return response()->json([
            'status' => true,
            'message' => 'The medications list has been successfully retrieved.',
            'data' => $medications
        ]);
    }
}

