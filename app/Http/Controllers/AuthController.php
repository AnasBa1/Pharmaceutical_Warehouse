<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'unique:users,username'],
            'phone_number' => ['required', 'unique:users,phone_number'],
            'password' => ['required']
            ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::query()->create([
           'username' => $request['username'],
           'phone_number' => $request['phone_number'],
           'password' => $request['password']
        ]);

        $token = $user->createToken('Storma')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'The user account has been created successfully.',
            'data' => [
                'user' => $user,
                'token' => $token
            ]]);
    }

    public function login (Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'digits:10', 'exists:users,phone_number'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Validate error.",
                'errors' => $validator->errors()
            ], 422);
        }

        if(!Auth::attempt($request->only(['phone_number', 'password']))) {
            return response()->json([
                'status' => false,
                'message' => "The phone number or password isn't correct.",
                'data' => []
            ], 500);
        }

        $user = User::query()
            ->where('phone_number', '=', $request['phone_number'])
            ->first();

        $token = $user->createToken('Storma')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'The user Logged in successfully.',
            'data' => [
                'user' => $user,
                'token' => $token
            ]]);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'The user Logged out successfully.',
            'data' => []
        ]);
    }
}