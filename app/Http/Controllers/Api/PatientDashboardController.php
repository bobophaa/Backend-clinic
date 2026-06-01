<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;

class PatientDashboardController extends Controller
{
    public function getHealthVitals(Request $request)
    {
        $user = $request->user();
        
        $profile = $user->profile ?? [];

        return response()->json([
            'status' => 'success',
            'data' => [
                'heart_rate' => 72, 
                'weight' => $profile['weight'] ?? 75.8, 
                'blood_sugar' => 95, 
                'weight_change' => '-0.0kg',
                'medicines' => [
                    ['drug_name' => 'Amoxicillin', 'strength' => '500mg', 'dosage' => 'បន្ទាប់ពីអាហារ', 'frequency' => '៣ដង/ថ្ងៃ'],
                    ['drug_name' => 'Paracetamol', 'strength' => '500mg', 'dosage' => 'ពេលមានអាការៈឈឺក្បាល', 'frequency' => 'រៀងរាល់ ៦ ម៉ោង']
                ]
            ]
        ], 200);
    }
}