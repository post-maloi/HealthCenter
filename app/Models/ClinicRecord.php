<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicRecord extends Model
{
// app/Models/ClinicRecord.php
// app/Models/ClinicRecord.php

protected $fillable = [
    'first_name', 'middle_name', 'last_name', 
    'consultation_date', 'birthday', 'gender', 
    'civil_status', 'contact_number', 'address_purok', 
    'age', 'diagnosis', 'medicines_given'
];
    // Inside app/Models/ClinicRecord.php

protected $casts = [
    'consultation_date' => 'date',
    'birthday' => 'date',
];
}