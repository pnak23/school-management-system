<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryFine extends Model
{
    use HasFactory;

    protected $table = 'library_fines';

    protected $fillable = [
        'loan_id',
        'user_id',
        'fine_type',
        'amount',
        'paid_amount',
        'status',
        'assessed_at',
        'paid_at',
        'note',
        'created_by',
        'updated_by',
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'assessed_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship: Fine belongs to a loan
     */
    public function loan()
    {
        return $this->belongsTo(LibraryLoan::class, 'loan_id');
    }

    /**
     * Relationship: Fine belongs to a user (person responsible for paying)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: User who created this fine
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: User who last updated this fine
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessor: Calculate balance (remaining amount to pay)
     */
    public function getBalanceAttribute()
    {
        $balance = $this->amount - $this->paid_amount;
        return max(0, $balance); // Never negative
    }

    /**
     * Accessor: Check if fine is fully paid
     */
    public function getIsPaidAttribute()
    {
        return $this->status === 'paid' || $this->balance <= 0;
    }

    /**
     * Scope: Active fines only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Inactive fines
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter by fine type
     */
    public function scopeFineType($query, $type)
    {
        if ($type && $type !== 'all') {
            return $query->where('fine_type', $type);
        }
        return $query;
    }

    /**
     * Get borrower name from loan if available
     */
    public function getBorrowerNameAttribute()
    {
        if ($this->loan && $this->loan->borrower_name) {
            return $this->loan->borrower_name;
        }
        return 'N/A';
    }

    /**
     * Get book title from loan if available
     */
    public function getBookTitleAttribute()
    {
        if ($this->loan && $this->loan->copy && $this->loan->copy->item) {
            return $this->loan->copy->item->title;
        }
        return 'N/A';
    }
}







