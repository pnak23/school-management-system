<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LibraryVisit extends Model
{
    use HasFactory;

    protected $table = 'library_visits';

    protected $fillable = [
        'user_id',
        'guest_id',
        'visit_date',
        'check_in_time',
        'check_out_time',
        'session',
        'purpose',
        'checked_in_by_staff_id',
        'checked_out_by_staff_id',
        'note',
        'created_by',
        'updated_by',
        'is_active'
    ];

    protected $casts = [
        'visit_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guest()
    {
        return $this->belongsTo(LibraryGuest::class, 'guest_id');
    }

    public function checkedInByStaff()
    {
        return $this->belongsTo(Staff::class, 'checked_in_by_staff_id');
    }

    public function checkedOutByStaff()
    {
        return $this->belongsTo(Staff::class, 'checked_out_by_staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visit_date', Carbon::today());
    }

    public function scopeOpenSessions($query)
    {
        return $query->whereNull('check_out_time');
    }

    // Accessors
    public function getVisitorNameAttribute()
    {
        if ($this->user_id) {
            return $this->user ? $this->user->name : 'Unknown User';
        } elseif ($this->guest_id) {
            return $this->guest ? $this->guest->full_name : 'Unknown Guest';
        }
        return 'N/A';
    }

    public function getVisitorTypeAttribute()
    {
        if ($this->user_id) {
            return 'user';
        } elseif ($this->guest_id) {
            return 'guest';
        }
        return null;
    }

    public function getIsOpenAttribute()
    {
        return is_null($this->check_out_time);
    }

    public function getDurationAttribute()
    {
        if (!$this->check_in_time) {
            return null;
        }

        $end = $this->check_out_time ?? Carbon::now();
        return $this->check_in_time->diffForHumans($end, true);
    }
}






