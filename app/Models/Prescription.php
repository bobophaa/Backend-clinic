<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

  // app/Models/Prescription.php
protected $fillable = [
    'medical_record_id',
    'medicine_name',
    'dosage',
    'frequency',
    'duration_days',
    'instructions',
];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function toFrontendArray(): array
    {
        $medicalRecord = $this->relationLoaded('medicalRecord') ? $this->medicalRecord : $this->medicalRecord()->first();
        
        return [
            'id' => $this->id,
            'medical_record_id' => $this->medical_record_id,
            'drug_name' => $this->medicine_name,
            'medicine_name' => $this->medicine_name,
            'dosage' => $this->dosage,
            'frequency' => $this->frequency,
            'duration_days' => $this->duration_days,
            'instructions' => $this->instructions,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'medical_record' => $medicalRecord ? $medicalRecord->toFrontendArray() : null,
        ];
    }
}