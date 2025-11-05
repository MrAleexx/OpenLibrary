<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'image',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_category')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeLeaf($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Methods
     */
    public function getBooksCountAttribute(): int
    {
        return $this->books()->count();
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;

        while ($category) {
            $breadcrumb[] = $category;
            $category = $category->parent;
        }

        return array_reverse($breadcrumb);
    }

    public function getFullPathAttribute(): string
    {
        return $this->breadcrumb->pluck('name')->implode(' > ');
    }

    public function getLevelAttribute(): int
    {
        $level = 0;
        $category = $this;

        while ($category->parent) {
            $level++;
            $category = $category->parent;
        }

        return $level;
    }
}
