@php
    $editing = isset($user);
@endphp

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">First Name *</label>
        <input name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" class="w-full border rounded-lg px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Middle Name</label>
        <input name="middle_name" value="{{ old('middle_name', $user->middle_name ?? '') }}" class="w-full border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Last Name *</label>
        <input name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" class="w-full border rounded-lg px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Suffix</label>
        <input name="suffix" value="{{ old('suffix', $user->suffix ?? '') }}" class="w-full border rounded-lg px-3 py-2" placeholder="Jr., Sr., III">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email *</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border rounded-lg px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Role *</label>
        <select name="role" class="w-full border rounded-lg px-3 py-2" required>
            @foreach(['admin' => 'Admin', 'bhw' => 'BHW', 'nurse' => 'Nurse', 'doctor' => 'Doctor'] as $roleValue => $roleLabel)
                <option value="{{ $roleValue }}" @selected(old('role', $user->role ?? 'bhw') === $roleValue)>{{ $roleLabel }}</option>
            @endforeach
        </select>
    </div>
    @unless($editing)
    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Password *</label>
        <input type="text" name="password" value="{{ old('password') }}" class="w-full border rounded-lg px-3 py-2" required>
    </div>
    @endunless

    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Add Profile (Optional)</label>
        <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp" class="w-full border rounded-lg px-3 py-2 bg-white">
        <p class="text-xs text-slate-500 mt-1">Accepted: JPG, PNG, WEBP (max 2MB)</p>

        @if($editing && !empty($user->profile_photo_path))
            <div class="mt-3 flex items-center gap-3">
                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="Profile photo" class="w-14 h-14 rounded-full object-cover border border-slate-200">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remove_profile_photo" value="1">
                    Remove current profile photo
                </label>
            </div>
        @endif
    </div>
</div>
<label class="inline-flex items-center gap-2 mt-4">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
    <span class="text-sm">Active Account</span>
</label>
