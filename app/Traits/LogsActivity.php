<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log an activity
     *
     * @param string $description
     * @param mixed $subject The model instance being acted upon
     * @param array|null $properties Additional properties to log
     * @param string|null $logName Custom log name (default: 'default')
     * @return ActivityLog
     */
    public function logActivity($description, $subject = null, $properties = null, $logName = null)
    {
        $activity = new ActivityLog();
        $activity->log_name = $logName ?? config('activitylog.default_log_name', 'default');
        $activity->description = $description;
        
        if ($subject) {
            $activity->subject_type = get_class($subject);
            $activity->subject_id = $subject->id;
        }
        
        if (Auth::check()) {
            $activity->causer_type = 'App\Models\User';
            $activity->causer_id = Auth::id();
        }
        
        if ($properties) {
            $activity->properties = $properties;
        }
        
        $activity->save();
        
        return $activity;
    }

    /**
     * Log activity with old and new attributes (for updates)
     *
     * @param string $description
     * @param mixed $subject The model instance
     * @param array $oldAttributes Old attribute values
     * @param array $newAttributes New attribute values
     * @param string|null $logName
     * @return ActivityLog
     */
    public function logActivityUpdate($description, $subject, $oldAttributes, $newAttributes, $logName = null)
    {
        $properties = [
            'old' => $oldAttributes,
            'attributes' => $newAttributes
        ];
        
        return $this->logActivity($description, $subject, $properties, $logName);
    }
}








