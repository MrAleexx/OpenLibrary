<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BookLoan;
use App\Models\UserDownload;
use App\Models\BookReservation;


class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'avatar',
        'email',
        'institutional_email',
        'microsoft_id',
        'password',
        'dni',
        'phone',
        'user_type',
        'institutional_id',
        'membership_expires_at',
        'max_concurrent_loans',
        'can_download',
        'is_temp_password',
        'temp_password_expires_at',
        'downloads_today',
        'last_download_reset',
        'created_by',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
            'is_temp_password' => 'boolean',
            'temp_password_expires_at' => 'datetime',
            'membership_expires_at' => 'date',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'can_download' => 'boolean',
            'max_concurrent_loans' => 'integer',
            'downloads_today' => 'integer',
            'last_download_reset' => 'date',
        ];
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(UserDownload::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithTempPassword($query)
    {
        return $query->where('is_temp_password', true)
            ->where('temp_password_expires_at', '>', now());
    }

    public function scopeCanDownload($query)
    {
        return $query->where('can_download', true);
    }

    /**
     * Methods
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }

    public function hasMembershipExpired(): bool
    {
        return $this->membership_expires_at && $this->membership_expires_at->isPast();
    }

    public function hasTempPasswordExpired(): bool
    {
        return $this->is_temp_password &&
            $this->temp_password_expires_at &&
            $this->temp_password_expires_at->isPast();
    }

    public function getActiveLoansCount(): int
    {
        return $this->loans()->where('status', 'active')->count();
    }

    public function canBorrowMoreBooks(): bool
    {
        return $this->getActiveLoansCount() < $this->max_concurrent_loans;
    }

    public function resetDailyDownloads(): void
    {
        $this->update([
            'downloads_today' => 0,
            'last_download_reset' => now(),
        ]);
    }

    public function incrementDailyDownloads(): void
    {
        $this->increment('downloads_today');
    }

    public function hasReachedDownloadLimit(): bool
    {
        // Si es la primera descarga del día, resetear contador
        if (!$this->last_download_reset || $this->last_download_reset->isPast()) {
            $this->resetDailyDownloads();
            return false;
        }

        return $this->downloads_today >= 5; // Límite de 5 descargas diarias
    }
}
