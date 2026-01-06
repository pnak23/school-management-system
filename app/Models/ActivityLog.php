<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo('subject');
    }

    /**
     * Get the causer of the activity.
     */
    public function causer()
    {
        return $this->morphTo('causer');
    }

    /**
     * Get the user who caused the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Scope to filter by log name
     */
    public function scopeInLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by subject
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id);
    }
}








