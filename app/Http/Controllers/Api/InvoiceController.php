<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Invoice::with(['patient.user']);

        if ($user->role === 'patient') {
            $patient = Patient::where('user_id', $user->id)->first();
            if ($patient) {
                $query->where('patient_id', $patient->id);
            } else {
                return response()->json(['data' => []]);
            }
        }

        $invoices = $query->latest()->get()->map(fn($i) => [
            'id' => 'INV-' . str_pad($i->id, 4, '0', STR_PAD_LEFT),
            'dbId' => $i->id,
            'appointment_id' => $i->appointment_id,
            'patient_name' => $i->patient->user->name ?? null,
            'amount' => (float) $i->total_amount,
            'status' => $i->payment_status === 'paid' ? 'បានទូទាត់' : 'កំពុងរង់ចាំ',
            'payment_status' => $i->payment_status,
            'payment_method' => $i->payment_method,
            'date' => $i->created_at ? $i->created_at->toDateString() : null,
        ]);

        return response()->json(['data' => $invoices]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id|unique:billing,appointment_id',
            'patient_id' => 'required|exists:patients,id',
            'consultation_fee' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'payment_status' => 'required|in:paid,unpaid',
            'payment_method' => 'nullable|in:cash,card,transfer',
        ]);

        $validated['created_by'] = $request->user()->id;
        
        if ($validated['payment_status'] === 'paid') {
            $validated['paid_at'] = now();
        }

        $invoice = Invoice::create($validated);
        
        return response()->json(['data' => $invoice], 201);
    }
}
