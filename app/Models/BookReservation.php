<?php
// app/Models/BookReservation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'physical_copy_id',
        'reservation_date',
        'pickup_deadline',
        'status',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'pickup_deadline' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function physicalCopy(): BelongsTo
    {
        return $this->belongsTo(PhysicalCopy::class);
    }

    public function loan(): HasOne
    {
        return $this->hasOne(BookLoan::class, 'reservation_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReadyForPickup($query)
    {
        return $query->where('status', 'ready_for_pickup');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'ready_for_pickup']);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                          ->where('pickup_deadline', '<', now());
                    });
    }

    /**
     * Methods
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReadyForPickup(): bool
    {
        return $this->status === 'ready_for_pickup';
    }

    public function isPickedUp(): bool
    {
        return $this->status === 'picked_up';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->isPending() && $this->pickup_deadline->isPast());
    }

    public function markAsReadyForPickup(): void
    {
        $this->update(['status' => 'ready_for_pickup']);
    }

    public function markAsPickedUp(): void
    {
        $this->update(['status' => 'picked_up']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function canBeProcessed(): bool
    {
        return $this->isPending() || $this->isReadyForPickup();
    }

    public function isOverdue(): bool
    {
        return $this->pickup_deadline->isPast() && $this->isPending();
    }
}
