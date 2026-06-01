<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = MedicalRecord::with(['patient.user', 'doctor.user', 'prescriptions']);

        if ($user->role === 'patient') {
            $patient = Patient::where('user_id', $user->id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->id);
            } else {
                return response()->json(['data' => []]);
            }
        } elseif ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where('doctor_id', $doctor->id);
            }
        }
        // admin + receptionist → see all

        $records = $query->latest()->get()->map->toFrontendArray();
        return response()->json(['data' => $records]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id|unique:medical_records,appointment_id',
            'patient_id'     => 'required|exists:patients,id',
            'doctor_id'      => 'required|exists:doctors,id',
            'diagnosis'      => 'required|string',
            'symptoms'       => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $record = MedicalRecord::create($validated);

        // ✅ Auto-mark appointment as completed so receptionist can see it for payment
        Appointment::where('id', $validated['appointment_id'])
                   ->update(['status' => 'completed']);

        return response()->json([
            'data' => $record->load(['patient.user', 'doctor.user', 'prescriptions'])
                             ->toFrontendArray()
        ], 201);
    }

    public function storePrescriptions(Request $request, MedicalRecord $record)
    {
        $request->validate([
            'prescriptions'                 => 'required|array',
            'prescriptions.*.medicine_name' => 'required|string',
            'prescriptions.*.dosage'        => 'nullable|string',
            'prescriptions.*.frequency'     => 'nullable|string',
            'prescriptions.*.duration_days' => 'nullable|string',
            'prescriptions.*.instructions'  => 'nullable|string',
        ]);

        $record->prescriptions()->delete();

        foreach ($request->prescriptions as $p) {
            $record->prescriptions()->create([
                'medicine_name' => $p['medicine_name'],
                'dosage'        => $p['dosage'] ?? null,
                'frequency'     => $p['frequency'] ?? null,
                'duration_days' => isset($p['duration_days'])
                    ? (int) preg_replace('/[^0-9]/', '', $p['duration_days']) ?: null
                    : null,
                'instructions'  => $p['instructions'] ?? null,
            ]);
        }

        return response()->json([
            'data' => $record->fresh()
                ->load(['patient.user', 'doctor.user', 'prescriptions'])
                ->toFrontendArray()
        ]);
    }

    public function byAppointment($appointmentId)
    {
        $record = MedicalRecord::with(['patient.user', 'doctor.user', 'prescriptions'])
            ->where('appointment_id', $appointmentId)
            ->first();

        if (!$record) return response()->json(['data' => null]);

        return response()->json(['data' => $record->toFrontendArray()]);
    }
}