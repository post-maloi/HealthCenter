<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_records', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); 
            $table->string('middle_name')->nullable(); 
            $table->string('last_name');
            $table->date('consultation_date');
            $table->date('birthday');
            $table->string('gender');
            $table->string('civil_status');   
            $table->string('contact_number')->nullable(); 
            $table->string('address_purok');  
            $table->string('age'); 
            
            // Vital Signs
            $table->string('temp')->nullable();
            $table->string('bp')->nullable();
            $table->string('pr')->nullable();
            $table->string('rr')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('bmi')->nullable();
            
            // SOAP & Diagnosis
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('diagnosis'); 
            $table->text('medicines_given')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_records');
    }
};