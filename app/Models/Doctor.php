<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialization',
        'license_number',
        'phone',
        'room',
        'experience',
        'fee',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fee' => 'float',
    ];

    protected $appends = ['doctor_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function getDoctorCodeAttribute()
    {
        return 'DR-' . str_pad($this->id, 2, '0', STR_PAD_LEFT);
    }

    public function toFrontendArray(): array
    {
        $user = $this->relationLoaded('user') ? $this->user : $this->user()->first();

        $isActive = isset($this->is_active) ? (bool) $this->is_active : (isset($this->active) ? (bool) $this->active : true);

        return [
            'id' => $this->id,
            'dbId' => $this->id,
            'displayId' => $this->doctor_code,
            'name' => $user->name ?? null,
            'email' => $user->email ?? null,
            'specialty' => $this->specialization ?? $this->specialty ?? null,
            'specialization' => $this->specialization ?? $this->specialty ?? null,
            'phone' => $this->phone ?? null,
            'room' => $this->room ?? null,
            'experience' => $this->experience ?? null,
            'fee' => is_numeric($this->fee) ? (float) $this->fee : $this->fee,
            'active' => $isActive,
            'is_active' => $isActive,
        ];
    }
}