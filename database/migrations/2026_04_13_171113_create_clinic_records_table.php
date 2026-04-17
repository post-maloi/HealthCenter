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
        // Use separate name fields as requested
        $table->string('first_name'); 
        $table->string('middle_name')->nullable(); 
        $table->string('last_name');
        
        $table->date('consultation_date');
        $table->date('birthday');
        $table->string('gender');
        $table->string('civil_status');   
        $table->string('contact_number')->nullable(); 
        $table->string('address_purok');  
        $table->string('age'); // Keep as string to avoid the integer error
        $table->text('diagnosis')->nullable();
        $table->text('medicines_given')->nullable();
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
