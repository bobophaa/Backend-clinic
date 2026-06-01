<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'billing';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'created_by',
        'consultation_fee',
        'total_amount',
        'payment_status',
        'payment_method',
        'paid_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}