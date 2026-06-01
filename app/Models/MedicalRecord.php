<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'diagnosis',
        'symptoms',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function toFrontendArray(): array
    {
        return [
            'id'             => $this->id,
            'appointment_id' => $this->appointment_id,
            'patient_id'     => $this->patient_id,
            'doctor_id'      => $this->doctor_id,
            'patientName'    => $this->patient?->user?->name ?? '—',
            'doctorName'     => $this->doctor?->user?->name ?? '—',
            'diagnosis'      => $this->diagnosis,
            'symptoms'       => $this->symptoms,
            'notes'          => $this->notes,
            'prescriptions'  => $this->relationLoaded('prescriptions')
                ? $this->prescriptions->map(fn($p) => [
                    'id'            => $p->id,
                    'medicine_name' => $p->medicine_name,
                    'dosage'        => $p->dosage,
                    'frequency'     => $p->frequency,
                    'duration_days' => $p->duration_days,
                    'instructions'  => $p->instructions,
                ])->toArray()
                : [],
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}