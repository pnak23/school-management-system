<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryCopy extends Model
{
    use HasFactory;

    protected $table = 'library_copies';

    protected $fillable = [
        'library_item_id',
        'barcode',
        'call_number',
        'shelf_id',
        'acquired_date',
        'condition',
        'status',
        'created_by',
        'updated_by',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'acquired_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship: Copy belongs to Library Item
    public function item()
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    // Relationship: Copy belongs to Shelf
    public function shelf()
    {
        return $this->belongsTo(LibraryShelf::class, 'shelf_id');
    }

    // Relationship: Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship: Updater
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relationship: Copy may have many status history records
    public function statusHistory()
    {
        return $this->hasMany(LibraryCopyStatusHistory::class, 'library_copy_id');
    }

    // Relationship: Copy may have many loans (if library_loans table exists)
    public function loans()
    {
        return $this->hasMany(LibraryLoan::class, 'library_copy_id');
    }

    // Scope: Active copies only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Inactive copies
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Scope: Available copies
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    // Scope: By barcode
    public function scopeByBarcode($query, $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    // Scope: By status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope: By condition
    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    // Check if copy has active loans
    public function hasActiveLoans()
    {
        // Check if library_loans table exists and has records for this copy
        if (schema()->hasTable('library_loans')) {
            return $this->loans()->whereIn('status', ['active', 'on_loan', 'borrowed'])->exists();
        }
        return false;
    }

    // Check if copy can be deleted
    public function canBeDeleted()
    {
        // Check if it has any loan history
        if (schema()->hasTable('library_loans')) {
            return !$this->loans()->exists();
        }
        return true;
    }
}

