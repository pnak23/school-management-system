<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * LibraryReadingLog Model
 * 
 * Tracks in-library reading activity during visits.
 * Records what books/items visitors read while inside the library.
 * 
 * Business Logic:
 * - Must be linked to a valid LibraryVisit
 * - Tracks start_time and end_time for reading duration
 * - Calculates minutes_read automatically
 * - Counts unique books read per visit
 */
class LibraryReadingLog extends Model
{
    use HasFactory;

    protected $table = 'library_reading_logs';

    protected $fillable = [
        'visit_id',
        'library_item_id',
        'copy_id',
        'start_time',
        'end_time',
        'reading_type',
        'note',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    /**
     * The visit this reading log belongs to
     */
    public function visit()
    {
        return $this->belongsTo(LibraryVisit::class, 'visit_id');
    }

    /**
     * The library item (book) being read
     */
    public function item()
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    /**
     * Optional: specific copy being read (if tracked by barcode)
     */
    public function copy()
    {
        return $this->belongsTo(LibraryCopy::class, 'copy_id');
    }

    /**
     * User who created this log
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated this log
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessors & Computed Attributes
     */

    /**
     * Calculate reading duration in minutes
     * Returns null if start_time or end_time is not set
     * Always returns positive value
     * 
     * @return int|null
     */
    public function getMinutesReadAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        // Use abs() to ensure positive value
        // diffInMinutes with false parameter returns absolute difference
        return abs($this->start_time->diffInMinutes($this->end_time, false));
    }

    /**
     * Format reading duration as human-readable string
     * 
     * @return string
     */
    public function getDurationAttribute()
    {
        $minutes = $this->minutes_read;

        if ($minutes === null) {
            return 'N/A';
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins > 0) {
            return $hours . 'h ' . $mins . 'm';
        }
        
        return $hours . 'h';
    }

    /**
     * Check if reading is currently in progress (started but not stopped)
     * 
     * @return bool
     */
    public function getIsRunningAttribute()
    {
        return !is_null($this->start_time) && is_null($this->end_time);
    }

    /**
     * Get visitor name (from visit's user or guest)
     * 
     * @return string
     */
    public function getVisitorNameAttribute()
    {
        if (!$this->visit) {
            return 'Unknown';
        }

        if ($this->visit->user) {
            return $this->visit->user->name ?? 'N/A';
        }

        if ($this->visit->guest) {
            return $this->visit->guest->full_name ?? 'N/A';
        }

        return 'N/A';
    }

    /**
     * Scopes
     */

    /**
     * Scope to filter active logs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter inactive logs
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to filter by date range (via visit_date in library_visits)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from Date from (Y-m-d)
     * @param string $to Date to (Y-m-d)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereHas('visit', function ($q) use ($from, $to) {
            $q->whereBetween('visit_date', [$from, $to]);
        });
    }

    /**
     * Scope to filter by session (via library_visits.session)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $session
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySession($query, $session)
    {
        return $query->whereHas('visit', function ($q) use ($session) {
            $q->where('session', $session);
        });
    }

    /**
     * Scope to filter by visitor type (user/guest via library_visits)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type 'user' or 'guest'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByVisitorType($query, $type)
    {
        return $query->whereHas('visit', function ($q) use ($type) {
            if ($type === 'user') {
                $q->whereNotNull('user_id');
            } elseif ($type === 'guest') {
                $q->whereNotNull('guest_id');
            }
        });
    }

    /**
     * Scope to filter by library item
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $itemId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('library_item_id', $itemId);
    }

    /**
     * Scope to filter by visit
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $visitId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByVisit($query, $visitId)
    {
        return $query->where('visit_id', $visitId);
    }

    /**
     * Scope to filter running (in-progress) logs
     */
    public function scopeRunning($query)
    {
        return $query->whereNotNull('start_time')->whereNull('end_time');
    }

    /**
     * Scope to filter completed logs
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('start_time')->whereNotNull('end_time');
    }

    /**
     * Static Helper: Count unique books read in a visit
     * 
     * @param int $visitId
     * @return int
     */
    public static function countBooksReadInVisit($visitId)
    {
        return self::where('visit_id', $visitId)
            ->where('is_active', true)
            ->distinct('library_item_id')
            ->count('library_item_id');
    }

    /**
     * Static Helper: Get total minutes read in a visit
     * 
     * @param int $visitId
     * @return int
     */
    public static function totalMinutesInVisit($visitId)
    {
        $logs = self::where('visit_id', $visitId)
            ->where('is_active', true)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get();

        return $logs->sum(function ($log) {
            return $log->minutes_read ?? 0;
        });
    }
}


