<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
   public function register(Request $request)
    {
       
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        return DB::transaction(function () use ($request) {
            

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'patient', 
            ]);

    
            Patient::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'date_of_birth' => $request->dob,
                'gender' => $this->normalizeGender($request->gender) ?? 'male',
                'address' => $request->address,
                'national_id' => $request->nationalId,
                'occupation' => $request->occupation,
                'blood_type' => $request->bloodType,
                'weight_kg' => $request->weight,
                'height' => $request->height,
                'allergies' => $request->allergies,
                'chronic_disease' => $request->chronicDisease,
                'medical_history' => $request->medicalHistory,
            ]);

            
            $token = $user->createToken('auth_token')->plainTextToken;

            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
            ], 201);
        }); 
        
       
    }
 
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'អ៊ីមែល ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវឡើយ'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 200);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Find user by email and verify password directly (no session needed)
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Ensure the user has the admin role
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Not authorized as admin',
            ], 403);
        }

        // Create a Sanctum token scoped for admin usage
        $token = $user->createToken('admin-token')->plainTextToken;
        return response()->json([
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    private function normalizeGender(?string $gender): ?string
    {
        $gender = trim((string) $gender);

        $map = [
            'ប្រុស' => 'male',
            'ស្រី' => 'female',
            'male' => 'male',
            'female' => 'female',
            'other' => 'other',
        ];

        return $map[strtolower($gender)] ?? ($map[$gender] ?? null);
    }
}