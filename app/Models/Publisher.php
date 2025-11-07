<?php
// app/Models/Publisher.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'country',
        'website',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Methods
     */
    public function getBooksCountAttribute(): int
    {
        return $this->books()->count();
    }

    public function getLocationAttribute(): string
    {
        return $this->city ? "{$this->city}, {$this->country}" : $this->country;
    }
}
