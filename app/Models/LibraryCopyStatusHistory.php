<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryCopyStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'library_copy_status_history';

    protected $fillable = [
        'copy_id',
        'old_status',
        'new_status',
        'old_condition',
        'new_condition',
        'action',
        'note',
        'changed_by',
        'changed_at',
        'created_by',
        'updated_by',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship: History belongs to Copy
    public function copy()
    {
        return $this->belongsTo(LibraryCopy::class, 'copy_id');
    }

    // Relationship: Changed by user
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
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

    // Scope: Active history only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: For specific copy
    public function scopeForCopy($query, $copyId)
    {
        return $query->where('copy_id', $copyId);
    }

    // Scope: Recent changes first
    public function scopeRecent($query)
    {
        return $query->orderBy('changed_at', 'desc');
    }

    // Get formatted action text
    public function getActionTextAttribute()
    {
        $actions = [
            'status_change' => 'Status Changed',
            'condition_change' => 'Condition Changed',
            'status_condition_change' => 'Status & Condition Changed',
            'created' => 'Copy Created',
            'updated' => 'Copy Updated'
        ];

        return $actions[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    // Get formatted change summary
    public function getChangeSummaryAttribute()
    {
        $summary = [];

        if ($this->old_status !== $this->new_status) {
            $summary[] = "Status: {$this->old_status} → {$this->new_status}";
        }

        if ($this->old_condition !== $this->new_condition) {
            $summary[] = "Condition: {$this->old_condition} → {$this->new_condition}";
        }

        return implode(' | ', $summary);
    }
}









