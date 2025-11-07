<?php
// app/Models/LibraryClaim.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_type',
        'document_number',
        'email',
        'address',
        'claim_type',
        'subject',
        'description',
        'claimant_request',
        'status',
        'admin_response',
        'response_date',
    ];

    protected $casts = [
        'response_date' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'in_process');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Methods
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isInProcess(): bool
    {
        return $this->status === 'in_process';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function markAsInProcess(): void
    {
        $this->update(['status' => 'in_process']);
    }

    public function markAsResolved(string $response = null): void
    {
        $this->update([
            'status' => 'resolved',
            'admin_response' => $response,
            'response_date' => now(),
        ]);
    }

    public function markAsClosed(): void
    {
        $this->update(['status' => 'closed']);
    }

    public function getDocumentTypeDisplayAttribute(): string
    {
        return match($this->document_type) {
            'DNI' => 'DNI',
            'CE' => 'Carnet de Extranjería',
            'PASSPORT' => 'Pasaporte',
            default => $this->document_type,
        };
    }

    public function getClaimTypeDisplayAttribute(): string
    {
        return match($this->claim_type) {
            'complaint' => 'Queja',
            'claim' => 'Reclamo',
            'suggestion' => 'Sugerencia',
            default => ucfirst($this->claim_type),
        };
    }
}
