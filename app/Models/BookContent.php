<?php
// app/Models/BookContent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'chapter_title',
        'chapter_number',
        'page_start',
        'page_end',
        'description',
        'sort_order',
        'level',
    ];

    protected $casts = [
        'chapter_number' => 'integer',
        'page_start' => 'integer',
        'page_end' => 'integer',
        'sort_order' => 'integer',
        'level' => 'integer',
    ];

    /**
     * Relationships
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('chapter_number');
    }

    /**
     * Methods
     */
    public function getPageRangeAttribute(): string
    {
        if ($this->page_start && $this->page_end) {
            return "{$this->page_start}-{$this->page_end}";
        } elseif ($this->page_start) {
            return "{$this->page_start}";
        } else {
            return '';
        }
    }
}
