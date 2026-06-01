<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'room')) {
                $table->string('room')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('doctors', 'experience')) {
                $table->string('experience')->nullable()->after('room');
            }

            if (!Schema::hasColumn('doctors', 'fee')) {
                $table->decimal('fee', 10, 2)->default(0.00)->after('experience');
            }
        });
    }

    public function down(): void {
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'fee')) {
                $table->dropColumn('fee');
            }

            if (Schema::hasColumn('doctors', 'experience')) {
                $table->dropColumn('experience');
            }

            if (Schema::hasColumn('doctors', 'room')) {
                $table->dropColumn('room');
            }
        });
    }
};
