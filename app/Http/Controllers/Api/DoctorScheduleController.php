<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorSchedule::with('doctor.user');
        
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        
        $schedules = $query->get()->map(fn($s) => [
            'id' => $s->id,
            'doctor_id' => $s->doctor_id,
            'doctor_name' => $s->doctor->user->name ?? null,
            'day_of_week' => $s->day_of_week,
            'start_time' => $s->start_time,
            'end_time' => $s->end_time,
            'slot_duration_min' => $s->slot_duration_min,
            'is_available' => (bool)$s->is_available,
        ]);
        
        return response()->json(['data' => $schedules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'day_of_week' => 'required|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'start_time' => 'required',
            'end_time' => 'required',
            'slot_duration_min' => 'sometimes|integer',
            'is_available' => 'sometimes|boolean',
        ]);

        $schedule = DoctorSchedule::create($validated);
        
        return response()->json(['data' => $schedule], 201);
    }


    // Add to MedicalRecordController:
public function storePrescriptions(Request $request, MedicalRecord $record)
{
    $request->validate([
        'prescriptions'                => 'required|array',
        'prescriptions.*.medicine_name'=> 'required|string',
        'prescriptions.*.dosage'       => 'nullable|string',
        'prescriptions.*.frequency'    => 'nullable|string',
        'prescriptions.*.duration_days'=> 'nullable|integer',
        'prescriptions.*.instructions' => 'nullable|string',
    ]);

    $record->prescriptions()->delete();
    foreach ($request->prescriptions as $p) {
        $record->prescriptions()->create($p);
    }

    return response()->json([
        'data' => $record->fresh()->load(['patient.user', 'doctor.user', 'prescriptions'])->toFrontendArray()
    ]);
}
}
