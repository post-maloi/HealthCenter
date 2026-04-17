<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    \App\Models\Medicine::create([
        'name' => 'Alaxan FR (Ibuprofen + Paracetamol)',
        'batch_number' => 'B-2026-001',
        'stock' => 500,
        'expiration_date' => '2027-12-31',
    ]);

    \App\Models\Medicine::create([
        'name' => 'Amoxicillin 500mg',
        'batch_number' => 'B-2026-005',
        'stock' => 250,
        'expiration_date' => '2026-08-15',
    ]);
}
}
