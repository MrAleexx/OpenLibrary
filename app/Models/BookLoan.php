<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo BookLoan - Préstamo de libro físico
 * 
 * Representa un préstamo activo o histórico de una copia física
 * a un usuario específico del sistema.
 * 
 * Estados posibles:
 * - pending_pickup: Reserva lista para recoger
 * - ready_for_pickup: Lista para entregar
 * - active: Préstamo en curso
 * - overdue: Vencido
 * - returned: Devuelto
 * 
 * @property int $id
 * @property int $user_id
 * @property int $physical_copy_id
 * @property int|null $reservation_id
 * @property \Carbon\Carbon $loan_date
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $actual_return_date
 * @property int $renewal_count
 * @property string $status
 * @property string|null $notes
 */
class BookLoan extends Model
{
    use HasFactory;

    /**
     * Estados del préstamo
     */
    public const STATUS_PENDING_PICKUP = 'pending_pickup';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Límites de negocio
     */
    public const MAX_RENEWALS = 2;
    public const RENEWAL_DAYS = 15;

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

    // ===============================================
    // RELATIONSHIPS
    // ===============================================

    /**
     * Usuario que tiene el préstamo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Copia física prestada
     */
    public function physicalCopy(): BelongsTo
    {
        return $this->belongsTo(PhysicalCopy::class);
    }

    /**
     * Reserva que originó este préstamo (opcional)
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(BookReservation::class);
    }

    // ===============================================
    // QUERY SCOPES
    // ===============================================

    /**
     * Préstamos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Préstamos vencidos
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OVERDUE)
            ->orWhere(function (Builder $q) {
                $q->where('status', self::STATUS_ACTIVE)
                  ->where('due_date', '<', now());
            });
    }

    /**
     * Préstamos devueltos
     */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RETURNED);
    }

    /**
     * Préstamos pendientes de recoger
     */
    public function scopePendingPickup(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_PICKUP);
    }

    /**
     * Préstamos listos para entregar
     */
    public function scopeReadyForPickup(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_READY_FOR_PICKUP);
    }

    // ===============================================
    // QUERY HELPERS
    // ===============================================

    /**
     * Verificar si está activo
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verificar si está vencido
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE 
            || ($this->isActive() && $this->due_date && $this->due_date->isPast());
    }

    /**
     * Verificar si está devuelto
     */
    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    /**
     * Verificar si está pendiente de recoger
     */
    public function isPendingPickup(): bool
    {
        return $this->status === self::STATUS_PENDING_PICKUP;
    }

    /**
     * Verificar si está listo para recoger
     */
    public function isReadyForPickup(): bool
    {
        return $this->status === self::STATUS_READY_FOR_PICKUP;
    }

    /**
     * Verificar si está cancelado
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Verificar si puede ser activado (está en estado ready_for_pickup)
     */
    public function canBeActivated(): bool
    {
        return $this->isReadyForPickup();
    }

    /**
     * Verificar si puede ser cancelado (pendiente o listo)
     */
    public function canBeCancelled(): bool
    {
        return $this->isPendingPickup() || $this->isReadyForPickup();
    }

    /**
     * Verificar si puede renovarse
     */
    public function canBeRenewed(): bool
    {
        return $this->isActive() 
            && $this->renewal_count < self::MAX_RENEWALS
            && !$this->isOverdue();
    }

    // ===============================================
    // BUSINESS LOGIC METHODS
    // ===============================================

    /**
     * Marcar préstamo como devuelto
     * 
     * Actualiza estado y libera copia física
     */
    public function markAsReturned(): void
    {
        $this->update([
            'status' => self::STATUS_RETURNED,
            'actual_return_date' => now(),
        ]);
        
        $this->physicalCopy->update(['status' => 'available']);
        $this->physicalCopy->book->increment('available_physical_copies');
    }

    /**
     * Marcar préstamo como vencido
     */
    public function markAsOverdue(): void
    {
        if ($this->isActive()) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    /**
     * Marcar préstamo como listo para recoger
     * 
     * @param string|null $notes Notas opcionales del bibliotecario
     * @return void
     */
    public function markAsReadyForPickup(?string $notes = null): void
    {
        $updateData = ['status' => self::STATUS_READY_FOR_PICKUP];
        
        if ($notes) {
            $updateData['notes'] = $notes;
        }
        
        $this->update($updateData);
    }

    /**
     * Activar préstamo (entregar libro al usuario)
     * 
     * @param int $loanDays Días de préstamo (default: 15)
     * @param string|null $notes Notas opcionales
     * @return void
     */
    public function activate(int $loanDays = 15, ?string $notes = null): void
    {
        $updateData = [
            'status' => self::STATUS_ACTIVE,
            'loan_date' => now(),
            'due_date' => now()->addDays($loanDays),
        ];
        
        if ($notes) {
            $updateData['notes'] = $notes;
        }
        
        $this->update($updateData);
        
        // Actualizar estado de la copia física a 'loaned'
        $this->physicalCopy->update(['status' => 'loaned']);
    }

    /**
     * Cancelar/rechazar préstamo
     * 
     * @param string|null $reason Razón de la cancelación
     * @return void
     */
    public function cancel(?string $reason = null): void
    {
        $updateData = ['status' => self::STATUS_CANCELLED];
        
        if ($reason) {
            $updateData['notes'] = 'CANCELADO: ' . $reason;
        }
        
        $this->update($updateData);
        
        // Liberar copia física
        $this->physicalCopy->update(['status' => 'available']);
        $this->physicalCopy->book->increment('available_physical_copies');
    }

    /**
     * Renovar préstamo
     * 
     * @return bool True si se renovó exitosamente
     */
    public function renew(): bool
    {
        if (!$this->canBeRenewed()) {
            return false;
        }

        $this->update([
            'due_date' => $this->due_date->addDays(self::RENEWAL_DAYS),
            'renewal_count' => $this->renewal_count + 1,
        ]);

        return true;
    }

    /**
     * Obtener días de retraso
     * 
     * @return int Días vencidos (0 si no está vencido)
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Obtener días restantes hasta vencimiento
     * 
     * @return int|null Días restantes (negativo si vencido, null si no hay fecha)
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Verificar si está próximo a vencer
     * 
     * @param int $days Días de anticipación (default: 3)
     * @return bool
     */
    public function isDueSoon(int $days = 3): bool
    {
        if (!$this->isActive() || !$this->due_date) {
            return false;
        }

        $daysUntilDue = $this->getDaysUntilDue();
        return $daysUntilDue !== null && $daysUntilDue >= 0 && $daysUntilDue <= $days;
    }
}
