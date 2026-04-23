<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_records', function (Blueprint $table) {
            if (!Schema::hasColumn('clinic_records', 'condition_update')) {
                $table->string('condition_update', 30)->nullable()->after('diagnosis');
            }

            if (!Schema::hasColumn('clinic_records', 'follow_up_recommendation')) {
                $table->text('follow_up_recommendation')->nullable()->after('condition_update');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_records', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_records', 'follow_up_recommendation')) {
                $table->dropColumn('follow_up_recommendation');
            }
            if (Schema::hasColumn('clinic_records', 'condition_update')) {
                $table->dropColumn('condition_update');
            }
        });
    }
};

