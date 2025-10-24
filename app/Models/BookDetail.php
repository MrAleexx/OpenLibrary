<?php
// app/Models/BookDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'description',
        'edition',
        'file_format',
        'file_size',
        'reading_age',
        'deposito_legal',
        'restrictions',
        'notes',
        'original_language_code',
        'translation_language_code',
        'translator_name',
        'edition_number',
        'volume',
        'series',
        'physical_location',
        'keywords',
        'acquisition_date',
        'supplier',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function originalLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'original_language_code', 'code');
    }

    public function translationLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'translation_language_code', 'code');
    }

    /**
     * Methods
     */
    public function getKeywordsArrayAttribute(): array
    {
        return $this->keywords ? explode(',', $this->keywords) : [];
    }
}
