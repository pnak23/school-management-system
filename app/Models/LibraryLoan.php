<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LibraryLoan extends Model
{
    use HasFactory;

    protected $table = 'library_loans';

    protected $fillable = [
        'borrower_type',
        'borrower_id',
        'library_copy_id',
        'borrowed_at',
        'due_date',
        'returned_at',
        'processed_by_staff_id',
        'received_by_staff_id',
        'status',
        'note'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_date' => 'date',
        'returned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship: Loan belongs to Copy
    public function copy()
    {
        return $this->belongsTo(LibraryCopy::class, 'library_copy_id');
    }

    // Relationship: Processed by staff
    public function processedByStaff()
    {
        return $this->belongsTo(Staff::class, 'processed_by_staff_id');
    }

    // Relationship: Received by staff
    public function receivedByStaff()
    {
        return $this->belongsTo(Staff::class, 'received_by_staff_id');
    }

    // Get borrower (polymorphic-like)
    public function getBorrowerAttribute()
    {
        switch ($this->borrower_type) {
            case 'student':
                return \App\Models\Student::find($this->borrower_id);
            case 'teacher':
                return \App\Models\Teacher::find($this->borrower_id);
            case 'staff':
                return \App\Models\Staff::find($this->borrower_id);
            case 'guest':
                return \App\Models\LibraryGuest::find($this->borrower_id);
            default:
                return null;
        }
    }

    // Get borrower name
    public function getBorrowerNameAttribute()
    {
        $borrower = $this->borrower;
        if (!$borrower) return 'Unknown';

        // Try different name fields (prefer english_name for display)
        return $borrower->english_name 
            ?? $borrower->khmer_name 
            ?? $borrower->name 
            ?? 'Unknown';
    }

    // Get borrower identifier (code/ID)
    public function getBorrowerIdentifierAttribute()
    {
        $borrower = $this->borrower;
        if (!$borrower) return '';

        return $borrower->code 
            ?? $borrower->teacher_code 
            ?? $borrower->staff_code 
            ?? $borrower->phone 
            ?? '';
    }

    /**
     * Get the User associated with this borrower (for notifications)
     * 
     * @return \App\Models\User|null
     */
    public function borrowerUser()
    {
        $borrower = $this->borrower;
        if (!$borrower) return null;

        // Try to get user_id from borrower
        switch ($this->borrower_type) {
            case 'student':
                // Student model has user_id
                return $borrower->user_id ? \App\Models\User::find($borrower->user_id) : null;
            
            case 'teacher':
                // Teacher model has user_id
                return $borrower->user_id ? \App\Models\User::find($borrower->user_id) : null;
            
            case 'staff':
                // Staff model has user_id
                return $borrower->user_id ? \App\Models\User::find($borrower->user_id) : null;
            
            case 'guest':
                // Library guests typically don't have user accounts
                // But if they do, check for user_id field
                if (isset($borrower->user_id) && $borrower->user_id) {
                    return \App\Models\User::find($borrower->user_id);
                }
                return null;
            
            default:
                return null;
        }
    }

    // Check if loan is overdue
    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'borrowed' || $this->returned_at) {
            return false;
        }

        return $this->due_date && Carbon::parse($this->due_date)->isPast();
    }

    // Get computed status (includes overdue)
    public function getComputedStatusAttribute()
    {
        if ($this->is_overdue) {
            return 'overdue';
        }
        return $this->status;
    }

    // Get days overdue
    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) return 0;
        
        // Calculate positive days overdue
        return Carbon::parse($this->due_date)->diffInDays(Carbon::now(), false);
    }

    // Scope: Borrowed (not returned)
    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed')->whereNull('returned_at');
    }

    // Scope: Returned
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned')->whereNotNull('returned_at');
    }

    // Scope: Overdue
    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')
            ->whereNull('returned_at')
            ->where('due_date', '<', Carbon::today());
    }

    // Scope: By borrower type
    public function scopeByBorrowerType($query, $type)
    {
        return $query->where('borrower_type', $type);
    }

    // Scope: By borrower
    public function scopeByBorrower($query, $type, $id)
    {
        return $query->where('borrower_type', $type)->where('borrower_id', $id);
    }

    // Scope: By date range
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('borrowed_at', [$from, $to]);
    }
}

