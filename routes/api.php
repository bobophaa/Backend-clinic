<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PatientDashboardController;
use App\Http\Controllers\Api\BillingController;

// ── Health Check ──
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'ClinicSync API',
]));

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/doctors', [DoctorController::class, 'publicIndex']);
Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);

// ── Auth ──
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',       [AuthController::class, 'me']);
        Route::post('/logout',  [AuthController::class, 'logout']);
    });
});

// ==========================================
// PROTECTED ROUTES
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // ── Appointments ──
    Route::get('/appointments',                    [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}',               [AppointmentController::class, 'show']);
    Route::put('/appointments/{id}/approve',       [AppointmentController::class, 'approve']);
    Route::put('/appointments/{id}/reject',        [AppointmentController::class, 'reject']);
    Route::put('/appointments/{id}/complete',      [AppointmentController::class, 'complete']);

    // ── Doctors (Admin) ──
    Route::post('/admin/doctors',           [DoctorController::class, 'store']);
    Route::put('/admin/doctors/{doctor}',   [DoctorController::class, 'update']);
    Route::delete('/admin/doctors/{doctor}',[DoctorController::class, 'destroy']);
    Route::get('/doctors/{doctor}',         [DoctorController::class, 'show']);

    // ── Medical Records ──
    Route::get('/medical-records/appointment/{appointmentId}', [MedicalRecordController::class, 'byAppointment']);
    Route::get('/patient/medical-records',                     [MedicalRecordController::class, 'index']);
    Route::post('/patient/medical-records',                    [MedicalRecordController::class, 'store']);
    Route::post('/medical-records/{record}/prescriptions',     [MedicalRecordController::class, 'storePrescriptions']);

    // ── Prescriptions ──
    Route::get('/patient/prescriptions',  [PrescriptionController::class, 'index']);
    Route::post('/patient/prescriptions', [PrescriptionController::class, 'store']);

    // ── Billing ──
    Route::get('/billing/appointment/{appointmentId}', [BillingController::class, 'byAppointment']);
    Route::get('/billing',                             [BillingController::class, 'index']);
    Route::post('/billing',                            [BillingController::class, 'store']);
    Route::get('/billing/{id}',                        [BillingController::class, 'show']);

    // ── Doctor Schedules ──
    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);

    // ── Patients ──
    Route::get('/patients', [PatientController::class, 'index']);
Route::post('/patients', [PatientController::class, 'store']);
    // ── Dashboard ──
    Route::get('/dashboard/stats',       [DashboardController::class, 'stats']);
    Route::get('/patient/health-vitals', [PatientDashboardController::class, 'getHealthVitals']);
});