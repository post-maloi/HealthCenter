<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'first_name',
    'middle_name',
    'last_name',
    'suffix',
    'email',
    'password',
    'role',
    'is_active',
    'doctor_availability_override',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'doctor_availability_override' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        $legacyName = (string) ($this->attributes['name'] ?? '');
        if (trim($legacyName) !== '' && !$this->first_name && !$this->last_name) {
            return trim($legacyName);
        }

        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }

    public function getFormalNameAttribute(): string
    {
        $legacyName = (string) ($this->attributes['name'] ?? '');
        if (trim($legacyName) !== '' && !$this->first_name && !$this->last_name) {
            return trim($legacyName);
        }

        $middleInitial = $this->middle_name ? strtoupper(substr((string) $this->middle_name, 0, 1)) . '.' : '';

        return trim(implode(' ', array_filter([
            trim(implode(', ', array_filter([$this->last_name, $this->first_name]))),
            $middleInitial,
            $this->suffix,
        ])));
    }

    public function getIsDoctorAvailableAttribute(): bool
    {
        if ($this->role !== 'doctor') {
            return false;
        }

        if ($this->doctor_availability_override !== null) {
            return (bool) $this->doctor_availability_override;
        }

        return now()->isWednesday();
    }
}
