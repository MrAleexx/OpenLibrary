<?php
// app/Models/UserDownload.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'downloaded_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
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

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('downloaded_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('downloaded_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('downloaded_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }
}
