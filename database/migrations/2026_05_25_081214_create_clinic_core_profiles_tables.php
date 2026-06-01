<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Update users table with standard clinic structural fields
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'doctor', 'patient', 'receptionist'])->default('patient')->after('email');
            }
        });

        // 1. Create Patients Table
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 2. Create Doctors Table
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('specialization');
            $table->string('qualification')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('bio')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

        // 3. Create Receptionists Table
        Schema::create('receptionists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('phone', 20)->nullable();
            $table->enum('shift', ['morning', 'afternoon', 'full_day'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('receptionists');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('patients');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};