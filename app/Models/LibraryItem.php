<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LibraryItem extends Model
{
    protected $fillable = [
        'title',
        'isbn',
        'edition',
        'published_year',
        'language',
        'description',
        'cover_image',
        'category_id',
        'publisher_id',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_year' => 'integer',
    ];

    /**
     * Get the category that owns this item
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }

    /**
     * Get the publisher that published this item
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(LibraryPublisher::class, 'publisher_id');
    }

    /**
     * Get the authors of this item (many-to-many)
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(LibraryAuthor::class, 'library_author_item', 'library_item_id', 'author_id')
            ->withPivot('role', 'is_active', 'created_by', 'updated_by')
            ->withTimestamps();
    }

    /**
     * Get only active authors
     */
    public function activeAuthors(): BelongsToMany
    {
        return $this->authors()->wherePivot('is_active', 1);
    }

    /**
     * Get all physical copies of this item
     */
    public function copies(): HasMany
    {
        return $this->hasMany(LibraryCopy::class, 'library_item_id');
    }

    /**
     * Get the user who created this item
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this item
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Get only active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get only inactive items
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the cover image URL
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            // Check if file exists in storage
            if (Storage::disk('public')->exists($this->cover_image)) {
                return asset('storage/' . $this->cover_image);
            }
            // File doesn't exist, return null
            return null;
        }
        return null;
    }
}

