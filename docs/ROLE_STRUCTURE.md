# Role-Based Code Structure

Use this map to quickly locate code by account role.

## Controllers

- `app/Http/Controllers/Admin/*` - admin-only controllers
- `app/Http/Controllers/Doctor/DashboardController.php` - doctor dashboard controller
- `app/Http/Controllers/Nurse/DashboardController.php` - nurse dashboard controller
- `app/Http/Controllers/Bhw/DashboardController.php` - bhw dashboard controller
- `app/Http/Controllers/DoctorClinicRecordController.php` - doctor/nurse record workflow
- `app/Http/Controllers/ClinicRecordController.php` - bhw record workflow

## Models

- `app/Models/ClinicRecord.php` now includes role-oriented query scopes:
  - `latestPerPatient()`
  - `forBhwDashboard()`
  - `forDoctorNurseDashboard()`

## Views

- `resources/views/admin/*` - admin views
- `resources/views/doctor/*` - doctor views
- `resources/views/doctor/dashboard/index.blade.php` - doctor dashboard
- `resources/views/nurse/dashboard/index.blade.php` - nurse dashboard
- `resources/views/bhw/dashboard/index.blade.php` - bhw dashboard

Legacy files remain for compatibility, but role folders above are now the primary locations.
