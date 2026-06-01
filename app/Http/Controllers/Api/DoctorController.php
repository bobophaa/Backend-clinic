<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
   
    public function publicIndex(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $query = Doctor::with('user');
        
        if (!$user || $user->role !== 'admin') {
            $query->where('is_active', true);
        }
        
        $doctors = $query->get()->map->toFrontendArray();

        return response()->json(['data' => $doctors]);
    }

  
    public function index(): JsonResponse
    {
        $doctors = Doctor::with('user')->get()->map->toFrontendArray();

        return response()->json(['data' => $doctors]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'], 
            'specialization' => ['required', 'string', 'max:255'], 
            'phone' => ['nullable', 'string', 'max:30'],
            'room' => ['nullable', 'string', 'max:50'],
            'experience' => ['nullable', 'string', 'max:50'],
            'fee' => ['nullable', 'numeric'],
            'is_active' => ['sometimes', 'boolean'], 
        ]);

        $doctor = DB::transaction(function () use ($validated) {
         
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'doctor',
            ]);

          
            return Doctor::create([
                'user_id' => $user->id,
                'specialization' => $validated['specialization'],
                'phone' => $validated['phone'] ?? null,
                'room' => $validated['room'] ?? null,
                'experience' => $validated['experience'] ?? null,
                'fee' => $validated['fee'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
            ]);
        });

        return response()->json(['data' => $doctor->toFrontendArray()], 201);
    }

   
    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json(['data' => $doctor->load('user')->toFrontendArray()]);
    }

   
    public function update(Request $request, Doctor $doctor): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $doctor->user_id],
            'specialization' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'room' => ['nullable', 'string', 'max:50'],
            'experience' => ['nullable', 'string', 'max:50'],
            'fee' => ['nullable', 'numeric'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $doctor) {
         
            if (isset($validated['name']) || isset($validated['email'])) {
                $doctor->user->update(array_filter([
                    'name' => $validated['name'] ?? null,
                    'email' => $validated['email'] ?? null,
                ]));
            }

        
            $doctor->update(array_filter([
                'specialization' => $validated['specialization'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'room' => $validated['room'] ?? null,
                'experience' => $validated['experience'] ?? null,
                'fee' => $validated['fee'] ?? null,
                'is_active' => $validated['is_active'] ?? null,
            ], function ($value) { return $value !== null; }));
        });

        return response()->json(['data' => $doctor->fresh()->load('user')->toFrontendArray()]);
    }

    
    public function destroy(Request $request, Doctor $doctor): JsonResponse
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($doctor) {
          
            $doctor->user()->delete();
            $doctor->delete();
        });

        return response()->json(['message' => 'លុបទិន្នន័យគ្រូពេទ្យរួចរាល់']);
    }

    
    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}