<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{

    public function index()
    {
        $bills = Billing::with(['patient.user', 'appointment.doctor.user', 'items'])
            ->latest()->get()->map->toFrontendArray();
        return response()->json(['data' => $bills]);
    }


    public function show($id)
    {
        $bill = Billing::with(['patient.user', 'appointment.doctor.user', 'items'])
            ->findOrFail($id);
        return response()->json(['data' => $bill->toFrontendArray()]);
    }

  
    public function byAppointment($appointmentId)
    {
        $bill = Billing::with(['patient.user', 'appointment.doctor.user', 'items'])
            ->where('appointment_id', $appointmentId)->first();
        if (!$bill) return response()->json(['data' => null]);
        return response()->json(['data' => $bill->toFrontendArray()]);
    }

  
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id'   => 'required|exists:appointments,id',
            'consultation_fee' => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,card,transfer',
            'items'            => 'array',
            'items.*.item_name'  => 'required|string',
            'items.*.item_type'  => 'required|in:test,medicine,service',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $appointment = Appointment::with('patient')->findOrFail($request->appointment_id);

        $bill = DB::transaction(function () use ($request, $appointment) {
            $itemsTotal = collect($request->items ?? [])->sum(fn($i) => $i['unit_price'] * $i['quantity']);
            $total = $request->consultation_fee + $itemsTotal;

            $bill = Billing::create([
                'appointment_id'   => $appointment->id,
                'patient_id'       => $appointment->patient_id,
                'created_by'       => auth()->id(),
                'consultation_fee' => $request->consultation_fee,
                'total_amount'     => $total,
                'payment_status'   => 'paid',
                'payment_method'   => $request->payment_method,
                'paid_at'          => now(),
            ]);

            foreach ($request->items ?? [] as $item) {
                $bill->items()->create($item);
            }

        
            $appointment->update(['status' => 'completed']);

            return $bill;
        });

        return response()->json([
            'success' => true,
            'data'    => $bill->load(['patient.user', 'appointment.doctor.user', 'items'])->toFrontendArray(),
        ], 201);
    }
}