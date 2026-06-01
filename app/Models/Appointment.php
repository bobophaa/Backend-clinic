<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'schedule_id',
        'booked_by',
        'appointment_date',
        'appointment_time',
        'status',
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function billing()
{
    return $this->hasOne(\App\Models\Billing::class);
}
public function toFrontendArray(): array
{
    $patient = $this->relationLoaded('patient') ? $this->patient : $this->patient()->with('user')->first();
    $doctor  = $this->relationLoaded('doctor')  ? $this->doctor  : $this->doctor()->with('user')->first();

    return [
        'id'               => $this->id,
        'patientId'        => $this->patient_id,
        'doctorId'         => $this->doctor_id,
        'scheduleId'       => $this->schedule_id,
        'patientName'      => $patient?->user?->name ?? 'Unknown Patient',
        'patientPhone'     => $patient?->user?->phone ?? $patient?->phone ?? '—',
        'patientDob'       => $patient?->date_of_birth ?? $patient?->dob ?? '—',
        'patientGender'    => $patient?->gender ?? '—',
        'patientBloodType' => $patient?->blood_type ?? '—',
        'patientAddress'   => $patient?->address ?? $patient?->user?->address ?? '—',
        'doctorName'       => $doctor?->user?->name  ?? 'Unknown Doctor',
        'department'       => $doctor?->specialization ?? '—',
        'appointment_date' => $this->appointment_date?->toDateString(),
        'appointment_time' => $this->appointment_time,
        'status'           => $this->status,
        'reason'           => $this->reason ?? '—',
        'notes'            => $this->notes ?? '—',
    ];
}
}