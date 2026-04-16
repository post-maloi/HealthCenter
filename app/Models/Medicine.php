<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    // Fillable allows these fields to be saved via the Controller
    protected $fillable = [
        'name', 
        'batch_number', 
        'stock', 
        'expiration_date', 
        'arrival_date'
    ];

    // Casting ensures Laravel treats these as Carbon date objects 
    // This is required for the ->format('M d, Y') function to work in your view
    protected $casts = [
        'expiration_date' => 'date',
        'arrival_date' => 'date',
    ];
}