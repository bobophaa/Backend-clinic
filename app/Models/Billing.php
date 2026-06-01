<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $table = 'billing';
    protected $fillable = [
        'appointment_id', 'patient_id', 'created_by',
        'consultation_fee', 'total_amount',
        'payment_status', 'payment_method', 'paid_at',
    ];

    public function appointment() { return $this->belongsTo(Appointment::class); }
    public function patient()     { return $this->belongsTo(Patient::class); }
    public function items()       { return $this->hasMany(BillingItem::class); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by'); }

    public function toFrontendArray(): array
    {
        return [
            'id'               => $this->id,
            'appointment_id'   => $this->appointment_id,
            'patient_id'       => $this->patient_id,
            'patientName'      => $this->patient?->user?->name ?? '—',
            'doctorName'       => $this->appointment?->doctor?->user?->name ?? '—',
            'appointment_date' => $this->appointment?->appointment_date?->toDateString(),
            'appointment_time' => $this->appointment?->appointment_time,
            'consultation_fee' => (float) $this->consultation_fee,
            'total_amount'     => (float) $this->total_amount,
            'payment_status'   => $this->payment_status,
            'payment_method'   => $this->payment_method,
            'paid_at'          => $this->paid_at,
            'items'            => $this->items->map(fn($i) => [
                'id'         => $i->id,
                'item_name'  => $i->item_name,
                'item_type'  => $i->item_type,
                'unit_price' => (float) $i->unit_price,
                'quantity'   => $i->quantity,
                'subtotal'   => (float) ($i->unit_price * $i->quantity),
            ])->toArray(),
        ];
    }
}