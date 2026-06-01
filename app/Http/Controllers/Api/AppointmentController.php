<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
       
        $request->validate([
            'doctor_id' => 'required',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
          
        ]);

        
        $patient_id = null;
        $booked_by = null;

        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            $patient = Patient::firstOrCreate([
                'user_id' => $userId,
            ], [
               
            ]);

            $patient_id = $patient->id;
            $booked_by = $userId;
        } else {
           
            $patient_id = $request->patient_id ?? null;
        }

        try {
            $appointment = DB::transaction(function () use ($request, $patient_id, $booked_by) {
                return Appointment::create([
                    'patient_id' => $patient_id,
                    'doctor_id' => $request->doctor_id,
                    'schedule_id' => $request->schedule_id ?? null,
                    'booked_by' => $booked_by,
                    'appointment_date' => $request->appointment_date,
                    'appointment_time' => $request->appointment_time,
                    'status' => 'pending', // default status
                    'notes' => $request->notes ?? null,
                    'reason' => $request->reason ?? $request->notes ?? 'ពិនិត្យជំងឺទូទៅ',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create appointment: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create appointment: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'ការកក់ណាត់ជួបត្រូវបានរក្សាទុកដោយជោគជ័យ!',
            'data' => $appointment->load(['patient.user', 'doctor.user'])->toFrontendArray()
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Appointment::with(['patient.user', 'doctor.user']);

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
            } else {
                return response()->json(['data' => []]);
            }
        }

        $appointments = $query->latest()->get()->map->toFrontendArray();
        
        return response()->json(['data' => $appointments]);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($id);
        
        return response()->json(['data' => $appointment->toFrontendArray()]);
    }

    public function approve($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'confirmed']);
        
        return response()->json([
            'success' => true,
            'data' => $appointment->fresh()->load(['patient.user', 'doctor.user'])->toFrontendArray()
        ]);
    }

    public function reject($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'cancelled']);
        
        return response()->json([
            'success' => true,
            'data' => $appointment->fresh()->load(['patient.user', 'doctor.user'])->toFrontendArray()
        ]);
    }

    public function complete($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'completed']);
        
        return response()->json([
            'success' => true,
            'data' => $appointment->fresh()->load(['patient.user', 'doctor.user'])->toFrontendArray()
        ]);
    }
}