<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('user')->latest()->get();

        return response()->json(
            $patients->map(function ($patient) {
                return [
                    'id'            => $patient->id,
                    'name'          => $patient->user->name ?? '',
                    'email'         => $patient->user->email ?? '',
                    'phone'         => $patient->phone,
                    'gender'        => $patient->gender,
                    'address'       => $patient->address,
                    'date_of_birth' => $patient->date_of_birth,
                    'blood_type'    => $patient->blood_type,
                    'weight_kg'     => $patient->weight_kg,
                    'height'        => $patient->height,
                    'allergies'     => $patient->allergies,
                    'chronic_disease' => $patient->chronic_disease,
                    'national_id'   => $patient->national_id,
                    'occupation'    => $patient->occupation,
                ];
            })
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|min:6',
            'phone'          => 'nullable|string',
            'gender'         => 'nullable|in:male,female,other',
            'date_of_birth'  => 'nullable|date',
            'address'        => 'nullable|string',
            'blood_type'     => 'nullable|string',
            'weight_kg'      => 'nullable|numeric',
            'height'         => 'nullable|numeric',
            'allergies'      => 'nullable|string',
            'chronic_disease'=> 'nullable|string',
            'national_id'    => 'nullable|string',
            'occupation'     => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'patient',
        ]);

        $patient = Patient::create([
            'user_id'        => $user->id,
            'phone'          => $request->phone,
            'gender'         => $request->gender,
            'date_of_birth'  => $request->date_of_birth,
            'address'        => $request->address,
            'blood_type'     => $request->blood_type,
            'weight_kg'      => $request->weight_kg,
            'height'         => $request->height,
            'allergies'      => $request->allergies,
            'chronic_disease'=> $request->chronic_disease,
            'national_id'    => $request->national_id,
            'occupation'     => $request->occupation,
        ]);

        return response()->json([
            'data' => [
                'id'            => $patient->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $patient->phone,
                'gender'        => $patient->gender,
                'date_of_birth' => $patient->date_of_birth,
                'address'       => $patient->address,
                'blood_type'    => $patient->blood_type,
                'weight_kg'     => $patient->weight_kg,
                'height'        => $patient->height,
                'allergies'     => $patient->allergies,
                'chronic_disease' => $patient->chronic_disease,
                'national_id'   => $patient->national_id,
                'occupation'    => $patient->occupation,
            ]
        ], 201);
    }
}