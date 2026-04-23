<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_address' => 'nullable|string|max:500',
            'queue_behavior' => 'nullable|string|max:255',
            'consultation_requires_doctor' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('clinic-assets', 'public');
            Setting::setValue('clinic_logo', $path);
        }

        Setting::setValue('clinic_name', $validated['clinic_name']);
        Setting::setValue('clinic_address', $validated['clinic_address'] ?? '');
        Setting::setValue('queue_behavior', $validated['queue_behavior'] ?? '');
        Setting::setValue('consultation_requires_doctor', $request->boolean('consultation_requires_doctor') ? '1' : '0');

        ActivityLogger::log('settings_updated', 'System settings updated', null, $request);

        return back()->with('success', 'Settings updated successfully.');
    }
}
