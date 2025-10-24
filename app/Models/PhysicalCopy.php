<?php
// app/Models/PhysicalCopy.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'barcode',
        'copy_number',
        'status',
        'location',
        'notes',
    ];

    protected $casts = [
        'copy_number' => 'integer',
    ];

    /**
     * Relationships
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
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
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeLoaned($query)
    {
        return $query->where('status', 'loaned');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeInMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Methods
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isLoaned(): bool
    {
        return $this->status === 'loaned';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    public function markAsLoaned(): void
    {
        $this->update(['status' => 'loaned']);
    }

    public function markAsReserved(): void
    {
        $this->update(['status' => 'reserved']);
    }

    public function markAsMaintenance(): void
    {
        $this->update(['status' => 'maintenance']);
    }

    public function getCurrentLoan(): ?BookLoan
    {
        return $this->loans()->where('status', 'active')->first();
    }

    public function getCurrentReservation(): ?BookReservation
    {
        return $this->reservations()->whereIn('status', ['pending', 'ready_for_pickup'])->first();
    }
}
