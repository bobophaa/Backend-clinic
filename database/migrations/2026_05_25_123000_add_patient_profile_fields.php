<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'national_id')) {
                $table->string('national_id')->nullable()->after('address');
            }
            if (!Schema::hasColumn('patients', 'occupation')) {
                $table->string('occupation')->nullable()->after('national_id');
            }
            if (!Schema::hasColumn('patients', 'blood_type')) {
                $table->string('blood_type')->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('patients', 'height')) {
                $table->decimal('height', 5, 2)->nullable()->after('weight_kg');
            }
            if (!Schema::hasColumn('patients', 'allergies')) {
                $table->text('allergies')->nullable()->after('height');
            }
            if (!Schema::hasColumn('patients', 'chronic_disease')) {
                $table->text('chronic_disease')->nullable()->after('allergies');
            }
            if (!Schema::hasColumn('patients', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('chronic_disease');
            }
        });
    }

    public function down(): void {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'national_id',
                'occupation',
                'blood_type',
                'height',
                'allergies',
                'chronic_disease',
                'medical_history',
            ]);
        });
    }
};
