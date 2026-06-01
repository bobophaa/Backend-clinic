<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Patient;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Prescription::with(['medicalRecord.patient.user', 'medicalRecord.doctor.user']);

        if ($user->role === 'patient') {
            $patient = Patient::where('user_id', $user->id)->first();
            if ($patient) {
                $query->whereHas('medicalRecord', function ($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                });
            } else {
                return response()->json(['data' => []]);
            }
        }

        $prescriptions = $query->latest()->get()->map->toFrontendArray();
        
        return response()->json(['data' => $prescriptions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'medicine_name' => 'required|string',
            'dosage' => 'nullable|string',
            'frequency' => 'nullable|string',
            'duration_days' => 'nullable|integer',
            'instructions' => 'nullable|string',
        ]);

        $prescription = Prescription::create($validated);
        
        return response()->json([
            'data' => $prescription->load('medicalRecord')->toFrontendArray()
        ], 201);
    }

    public function up()
{
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->string('duration_days')->nullable()->change();
    });
}
}
