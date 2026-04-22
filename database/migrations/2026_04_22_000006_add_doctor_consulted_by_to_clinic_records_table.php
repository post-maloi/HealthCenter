<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_records', function (Blueprint $table) {
            $table->string('doctor_consulted_by')->nullable()->after('consulted_by');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_records', function (Blueprint $table) {
            $table->dropColumn('doctor_consulted_by');
        });
    }
};

