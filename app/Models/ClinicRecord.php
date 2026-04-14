<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicRecord extends Model
{
    protected $fillable = [
        'patient_name',
        'consultation_date',
        'birthday',
        'gender',
        'age',
        'diagnosis',
        'medicines_given',
    ];
    // Inside app/Models/ClinicRecord.php

protected $casts = [
    'consultation_date' => 'date',
    'birthday' => 'date',
];
}