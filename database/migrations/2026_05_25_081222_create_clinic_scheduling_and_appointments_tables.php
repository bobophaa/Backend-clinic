<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 4. Create Doctor Schedules Table
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->enum('day_of_week', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration_min')->default(30);
            $table->tinyInteger('is_available')->default(1);
            $table->timestamps();
        });

        // 5. Create Core Appointments Table
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('doctor_schedules')->onDelete('set null');
            $table->foreignId('booked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Enforce double booking block constraint
            $table->unique(['doctor_id', 'appointment_date', 'appointment_time'], 'uidx_doctor_slot');
        });
    }

    public function down(): void {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('doctor_schedules');
    }
};