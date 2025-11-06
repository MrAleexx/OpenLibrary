<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'publisher_id',
        'isbn',
        'language_code',
        'pages',
        'publication_year',
        'cover_image',
        'pdf_file',
        'is_active',
        'downloadable',
        'book_type',
        'physical_copies_count', // Cambiado de total_physical_copies
        'available_copies_count', // Cambiado de available_physical_copies
        'total_downloads',
        'total_views',
        'featured',
        'search_metadata',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'downloadable' => 'boolean',
        'featured' => 'boolean',
        'published_at' => 'datetime',
        'publication_year' => 'integer',
        'pages' => 'integer',
        'physical_copies_count' => 'integer',
        'available_copies_count' => 'integer',
        'total_downloads' => 'integer',
        'total_views' => 'integer',
    ];

    // Agregar URLs completas para las imágenes y PDFs
    protected $appends = ['cover_url', 'pdf_url'];

    /**
     * Relationships
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public function details(): HasOne
    {
        return $this->hasOne(BookDetail::class);
    }

    public function contributors(): HasMany
    {
        return $this->hasMany(BookContributor::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BookContent::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category')
            ->withTimestamps();
    }

    public function physicalCopies(): HasMany
    {
        return $this->hasMany(PhysicalCopy::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(BookReservation::class);
    }

    public function loans(): HasManyThrough
    {
        return $this->hasManyThrough(
            BookLoan::class,
            PhysicalCopy::class,
            'book_id',
            'physical_copy_id',
            'id',
            'id'
        );
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(UserDownload::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeDownloadable($query)
    {
        return $query->where('downloadable', true);
    }

    public function scopeDigital($query)
    {
        return $query->where('book_type', 'digital')->orWhere('book_type', 'both');
    }

    public function scopePhysical($query)
    {
        return $query->where('book_type', 'physical')->orWhere('book_type', 'both');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', "%{$search}%")
            ->orWhere('isbn', 'like', "%{$search}%")
            ->orWhereHas('contributors', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
    }

    /**
     * Methods
     */
    public function incrementViews(): void
    {
        $this->increment('total_views');
    }

    public function incrementDownloads(): void
    {
        $this->increment('total_downloads');
    }

    public function getAvailablePhysicalCopies(): int
    {
        return $this->physicalCopies()->where('status', 'available')->count();
    }

    public function hasAvailablePhysicalCopies(): bool
    {
        return $this->getAvailablePhysicalCopies() > 0;
    }

    public function isAvailableForLoan(): bool
    {
        return $this->book_type !== 'digital' && $this->hasAvailablePhysicalCopies();
    }

    public function getTotalLoansCount(): int
    {
        return $this->loans()->count();
    }

    public function getActiveLoansCount(): int
    {
        return $this->loans()->where('status', 'active')->count();
    }

    public function getOverdueLoansCount(): int
    {
        return $this->loans()->where('status', 'overdue')->count();
    }

    public function getAuthorsAttribute(): string
    {
        return $this->contributors()
            ->where('contributor_type', 'author')
            ->orderBy('sequence_number')
            ->get()
            ->pluck('full_name')
            ->join(', ');
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_file ? asset('storage/' . $this->pdf_file) : null;
    }

    /**
     * Actualizar contadores de copias físicas
     */
    public function updatePhysicalCopiesCount(): void
    {
        $this->update([
            'physical_copies_count' => $this->physicalCopies()->count(),
            'available_copies_count' => $this->physicalCopies()->where('status', 'available')->count(),
        ]);
    }
}
