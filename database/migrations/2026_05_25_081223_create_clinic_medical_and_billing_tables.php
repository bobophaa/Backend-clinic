<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 6. Medical Records Table
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->text('diagnosis')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Prescriptions Table
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->onDelete('cascade');
            $table->string('medicine_name');
            $table->string('dosage', 100)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->integer('duration_days')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // 8. Billing Header Table
        Schema::create('billing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('consultation_fee', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'card', 'transfer'])->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 9. Billing Ledger Items Table
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('billing')->onDelete('cascade');
            $table->string('item_name');
            $table->enum('item_type', ['test', 'medicine', 'service']);
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('billing_items');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medical_records');
    }
};