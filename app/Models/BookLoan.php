<?php
// app/Models/BookLoan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'physical_copy_id',
        'reservation_id',
        'loan_date',
        'due_date',
        'actual_return_date',
        'renewal_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'loan_date' => 'datetime',
        'due_date' => 'datetime',
        'actual_return_date' => 'datetime',
        'renewal_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function physicalCopy(): BelongsTo
    {
        return $this->belongsTo(PhysicalCopy::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(BookReservation::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                          ->where('due_date', '<', now());
                    });
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Methods
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->isActive() && $this->due_date->isPast());
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function markAsReturned(): void
    {
        $this->update([
            'status' => 'returned',
            'actual_return_date' => now(),
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->isActive()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function renew(): bool
    {
        // Lógica para renovar el préstamo
        if ($this->renewal_count < 2) { // Máximo 2 renovaciones
            $this->update([
                'due_date' => $this->due_date->addDays(15), // 15 días más
                'renewal_count' => $this->renewal_count + 1,
            ]);
            return true;
        }
        return false;
    }

    public function getDaysOverdue(): int
    {
        if ($this->isOverdue()) {
            return now()->diffInDays($this->due_date);
        }
        return 0;
    }
}
