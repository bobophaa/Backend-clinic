<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Appointment::query();
        $doctor = null;
        $patient = null;

        if ($user->role === 'patient') {
            $patient = Patient::where('user_id', $user->id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->id);
            } else {
                $query->whereRaw('0 = 1');
            }
        } elseif ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where(function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id)
                        ->orWhereNull('doctor_id');
                });
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        $appointments = (clone $query)->get();

        $today = now()->toDateString();
        $todayQuery = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time');

        if ($user->role === 'patient' && $patient) {
            $todayQuery->where('patient_id', $patient->id);
        } elseif ($user->role === 'doctor' && $doctor) {
            $todayQuery->where(function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->orWhereNull('doctor_id');
            });
        }

        $todayList = $todayQuery->get()->map->toFrontendArray();

        $recentQuery = Appointment::with(['patient', 'doctor'])->latest()->limit(5);
        if ($user->role === 'patient' && $patient) {
            $recentQuery->where('patient_id', $patient->id);
        } elseif ($user->role === 'doctor' && $doctor) {
            $recentQuery->where(function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)->orWhereNull('doctor_id');
            });
        }

        $recent = $recentQuery->get()->map->toFrontendArray();

        return response()->json([
            'stats' => [
                'appointmentsTotal' => $appointments->count(),
                'appointmentsPending' => $appointments->where('status', 'pending')->count(),
                'appointmentsApproved' => $appointments->whereIn('status', ['approved', 'confirmed'])->count(),
                'appointmentsCompleted' => $appointments->where('status', 'completed')->count(),
                'appointmentsRejected' => $appointments->whereIn('status', ['rejected', 'cancelled'])->count(),
                'patientsTotal' => $user->role === 'patient' ? 1 : User::where('role', 'patient')->count(),
                'doctorsTotal' => Doctor::where('is_active', true)->count(),
            ],
            'todayAppointments' => $todayList,
            'recentAppointments' => $recent,
        ]);
    }
}
