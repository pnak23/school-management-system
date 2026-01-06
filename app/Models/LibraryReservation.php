<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LibraryReservation extends Model
{
    use HasFactory;

    protected $table = 'library_reservations';

    protected $fillable = [
        'user_id',
        'library_item_id',
        'assigned_copy_id',
        'queue_no',
        'status',
        'reserved_at',
        'expires_at',
        'note',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'reserved_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relationships
     */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function libraryItem()
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    public function assignedCopy()
    {
        return $this->belongsTo(LibraryCopy::class, 'assigned_copy_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessors
     */

    public function getIsExpiredAttribute()
    {
        if ($this->status === 'ready' && $this->expires_at) {
            return Carbon::now()->greaterThan($this->expires_at);
        }
        return false;
    }

    /**
     * Query Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'ready')
            ->where('expires_at', '<', Carbon::now());
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('library_item_id', $itemId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Helper Methods
     */

    /**
     * Get next queue number for a specific item
     */
    public static function getNextQueueNumber($libraryItemId)
    {
        $maxQueue = self::where('library_item_id', $libraryItemId)
            ->where('status', 'pending')
            ->max('queue_no');

        return ($maxQueue ?? 0) + 1;
    }

    /**
     * Check if reservation is expired
     */
    public function isExpired()
    {
        return $this->is_expired;
    }

    /**
     * Mark reservation as cancelled
     */
    public function markAsCancelled($note = null)
    {
        $this->update([
            'status' => 'cancelled',
            'note' => $note ?? $this->note,
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Mark reservation as fulfilled
     */
    public function markAsFulfilled()
    {
        $this->update([
            'status' => 'fulfilled',
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Assign copy to reservation
     */
    public function assignCopy($copyId, $expiresInDays = 2)
    {
        $this->update([
            'assigned_copy_id' => $copyId,
            'status' => 'ready',
            'expires_at' => Carbon::now()->addDays((int) $expiresInDays),
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Auto-cancel expired ready reservations
     */
    public static function cancelExpiredReservations()
    {
        $expired = self::expired()->get();

        foreach ($expired as $reservation) {
            $reservation->markAsCancelled('Expired - not picked up in time');
        }

        return $expired->count();
    }
}

