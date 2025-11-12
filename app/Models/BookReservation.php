<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo BookReservation - Reserva de libro
 * 
 * Representa una reserva de libro en el sistema con gestión
 * de cola FIFO para libros sin disponibilidad inmediata.
 * 
 * Estados posibles:
 * - pending: En cola esperando disponibilidad
 * - ready: Lista para recoger (libro disponible)
 * - collected: Recogida y convertida a préstamo
 * - expired: Expirada por no recoger a tiempo
 * - cancelled: Cancelada por usuario o admin
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $book_id
 * @property int|null $physical_copy_id
 * @property \Carbon\Carbon $reservation_date
 * @property \Carbon\Carbon|null $pickup_deadline
 * @property \Carbon\Carbon|null $pickup_date
 * @property \Carbon\Carbon|null $cancellation_date
 * @property string $status
 * @property string|null $notes
 */
class BookReservation extends Model
{
    use HasFactory;

    /**
     * Estados de la reserva
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Días para recoger una reserva activada
     */
    public const PICKUP_DEADLINE_DAYS = 7;

    protected $fillable = [
        'user_id',
        'book_id',
        'physical_copy_id',
        'reservation_date',
        'pickup_deadline',
        'pickup_date',
        'cancellation_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'pickup_deadline' => 'datetime',
        'pickup_date' => 'datetime',
        'cancellation_date' => 'datetime',
    ];

    // ===============================================
    // RELATIONSHIPS
    // ===============================================

    /**
     * Usuario que hizo la reserva
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Libro reservado (puede ser null si usa physical_copy)
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Copia física específica (opcional)
     */
    public function physicalCopy(): BelongsTo
    {
        return $this->belongsTo(PhysicalCopy::class);
    }

    /**
     * Préstamo generado desde esta reserva
     */
    public function loan(): HasOne
    {
        return $this->hasOne(BookLoan::class, 'reservation_id');
    }

    // ===============================================
    // QUERY SCOPES
    // ===============================================

    /**
     * Reservas pendientes (en cola)
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Reservas listas para recoger
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_READY);
    }

    /**
     * Reservas activas (pending o ready)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_READY
        ]);
    }

    /**
     * Reservas expiradas
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED)
            ->orWhere(function (Builder $q) {
                $q->where('status', self::STATUS_READY)
                  ->where('pickup_deadline', '<', now());
            });
    }

    /**
     * Reservas de un libro específico (dual-source)
     */
    public function scopeForBook(Builder $query, int $bookId): Builder
    {
        return $query->where(function (Builder $q) use ($bookId) {
            $q->where('book_id', $bookId)
              ->orWhereHas('physicalCopy', function (Builder $sq) use ($bookId) {
                  $sq->where('book_id', $bookId);
              });
        });
    }

    // ===============================================
    // STATE CHECKERS
    // ===============================================

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si está lista para recoger
     */
    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    /**
     * Verificar si fue recogida
     */
    public function isCollected(): bool
    {
        return $this->status === self::STATUS_COLLECTED;
    }

    /**
     * Verificar si está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Verificar si está expirada
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED 
            || ($this->isReady() && $this->pickup_deadline?->isPast());
    }

    /**
     * Verificar si puede procesarse (activarse o convertir a préstamo)
     */
    public function canBeProcessed(): bool
    {
        return $this->isPending() || $this->isReady();
    }

    /**
     * Verificar si está próxima a expirar
     * 
     * @param int $days Días de anticipación (default: 2)
     * @return bool
     */
    public function isExpiringSoon(int $days = 2): bool
    {
        if (!$this->isReady() || !$this->pickup_deadline) {
            return false;
        }

        $daysUntilDeadline = now()->diffInDays($this->pickup_deadline, false);
        return $daysUntilDeadline >= 0 && $daysUntilDeadline <= $days;
    }

    // ===============================================
    // BUSINESS LOGIC METHODS
    // ===============================================

    /**
     * Activar reserva (pending → ready)
     * 
     * @param int|null $pickupDeadlineDays Días para deadline (default: constante)
     * @return void
     */
    public function markAsReady(?int $pickupDeadlineDays = null): void
    {
        $days = $pickupDeadlineDays ?? self::PICKUP_DEADLINE_DAYS;

        $this->update([
            'status' => self::STATUS_READY,
            'pickup_deadline' => now()->addDays($days),
        ]);
    }

    /**
     * Marcar como recogida
     */
    public function markAsCollected(): void
    {
        $this->update([
            'status' => self::STATUS_COLLECTED,
            'pickup_date' => now(),
        ]);
    }

    /**
     * Cancelar reserva
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_date' => now(),
        ]);
    }

    /**
     * Marcar como expirada
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    /**
     * Obtener libro asociado (dual-source)
     * 
     * @return Book|null
     */
    public function getBook(): ?Book
    {
        return $this->physicalCopy?->book ?? $this->book;
    }

    /**
     * Obtener ID del libro (dual-source)
     * 
     * @return int|null
     */
    public function getBookId(): ?int
    {
        return $this->physicalCopy?->book_id ?? $this->book_id;
    }

    /**
     * Calcular posición en cola FIFO
     * 
     * @return int Posición (1-indexed)
     */
    public function getQueuePosition(): int
    {
        if (!$this->isPending()) {
            return 0;
        }

        $bookId = $this->getBookId();
        if (!$bookId) {
            return 0;
        }

        return static::forBook($bookId)
            ->pending()
            ->where('reservation_date', '<', $this->reservation_date)
            ->count() + 1;
    }

    /**
     * Obtener total de reservas pending del mismo libro
     * 
     * @return int
     */
    public function getTotalInQueue(): int
    {
        $bookId = $this->getBookId();
        if (!$bookId) {
            return 0;
        }

        return static::forBook($bookId)
            ->pending()
            ->count();
    }
}
