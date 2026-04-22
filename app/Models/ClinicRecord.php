<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicRecord extends Model
{
    use HasFactory;
    private const DOCTOR_PLACEHOLDER_DIAGNOSIS = 'For doctor assessment';

    protected $casts = [
        'consultation_date' => 'date',
        'birthday' => 'date',
    ];

protected $fillable = [
    'first_name', 
    'middle_name', 
    'last_name', 
    'birthday', 
    'age', 
    'gender', 
    'civil_status', 
    'contact_number',
    'address_purok', 
    'consultation_date',
    'temp',   // Add this
    'bp',     // Add this
    'pr',     // Add this
    'rr',     // Add this
    'weight', // Add this
    'height', // Add this
    'bmi',    // Add this
    'subjective', 
    'objective', 
    'diagnosis',
    'medicines_given',
    'laboratory_image_path',
    'consulted_by',
    'doctor_consulted_by',
];
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'clinic_record_medicine')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function laboratoryFiles(): HasMany
    {
        return $this->hasMany(ClinicRecordFile::class, 'clinic_record_id');
    }

    public function getWorkflowStatusAttribute(): string
    {
        if (!empty($this->doctor_consulted_by) && trim((string) $this->diagnosis) !== self::DOCTOR_PLACEHOLDER_DIAGNOSIS) {
            return 'completed';
        }

        return 'waiting_for_doctor';
    }
}