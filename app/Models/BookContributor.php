<?php
// app/Models/BookContributor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookContributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'contributor_type',
        'full_name',
        'email',
        'sequence_number',
        'biographical_note',
    ];

    protected $casts = [
        'sequence_number' => 'integer',
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
    public function scopeAuthors($query)
    {
        return $query->where('contributor_type', 'author');
    }

    public function scopeEditors($query)
    {
        return $query->where('contributor_type', 'editor');
    }

    public function scopeTranslators($query)
    {
        return $query->where('contributor_type', 'translator');
    }

    /**
     * Methods
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->contributor_type) {
            'author' => 'Autor',
            'editor' => 'Editor',
            'translator' => 'Traductor',
            'illustrator' => 'Ilustrador',
            'prologuist' => 'Prologuista',
            default => ucfirst($this->contributor_type),
        };
    }
}
