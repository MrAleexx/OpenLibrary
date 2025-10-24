<?php
// app/Models/Language.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Book;
use App\Models\BookDetail;

class Language extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'native_name',
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
        return $this->hasMany(Book::class, 'language_code', 'code');
    }

    public function originalLanguageBooks(): HasMany
    {
        return $this->hasMany(BookDetail::class, 'original_language_code', 'code');
    }

    public function translationLanguageBooks(): HasMany
    {
        return $this->hasMany(BookDetail::class, 'translation_language_code', 'code');
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
    public function getDisplayNameAttribute(): string
    {
        return $this->native_name ?: $this->name;
    }
}
