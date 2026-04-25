<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'bhw', 'nurse', 'doctor'])],
            'password' => 'required|string|min:8',
            'is_active' => 'nullable|boolean',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'profile_photo_path' => $profilePhotoPath,
        ]);

        ActivityLogger::log('user_created', "Created user {$user->email}", $user, $request);

        return redirect()->route('admin.users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'bhw', 'nurse', 'doctor'])],
            'is_active' => 'nullable|boolean',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_profile_photo' => 'nullable|boolean',
        ]);

        // Prevent self-demotion to avoid accidental lockout of admin center.
        if ((int) $user->id === (int) auth()->id() && ($validated['role'] ?? '') !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.'])->withInput();
        }

        if ($request->boolean('remove_profile_photo') && $user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $validated['profile_photo_path'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        unset($validated['remove_profile_photo']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $user->update($validated);
        ActivityLogger::log('user_updated', "Updated user {$user->email}", $user, $request);

        return redirect()->route('admin.users.index')->with('success', 'User account updated.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => !$user->is_active]);
        $state = $user->is_active ? 'activated' : 'deactivated';
        ActivityLogger::log('user_status_changed', "User {$user->email} was {$state}", $user, $request);

        return back()->with('success', "User {$state} successfully.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);
        ActivityLogger::log('user_password_reset', "Password reset for {$user->email}", $user, $request);

        return back()->with('success', 'Password has been reset.');
    }
}
