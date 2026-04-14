<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // xxxx_xx_xx_create_clinic_records_table.php
public function up(): void
{
    Schema::create('clinic_records', function (Blueprint $table) {
        $table->id();
        $table->date('consultation_date');
        $table->string('patient_name');
        $table->string('gender');
        $table->integer('age');
        $table->date('birthday');
        $table->text('diagnosis');
        $table->text('medicines_given');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_records');
    }
};
