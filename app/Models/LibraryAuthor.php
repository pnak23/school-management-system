<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LibraryAuthor extends Model
{
    protected $fillable = [
        'name',
        'nationality',
        'dob',
        'biography',
        'phone',
        'email',
        'website',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this author
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this author
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all library items this author contributed to
     */
    public function libraryItems(): BelongsToMany
    {
        return $this->belongsToMany(LibraryItem::class, 'library_author_item', 'author_id', 'library_item_id')
            ->withPivot('role', 'is_active', 'created_by', 'updated_by')
            ->withTimestamps();
    }

    /**
     * Get only active library items
     */
    public function activeLibraryItems(): BelongsToMany
    {
        return $this->libraryItems()->wherePivot('is_active', 1);
    }

    /**
     * Scope: Get only active authors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope: Get only inactive authors
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}


