<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users assigned to this role.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withPivot('is_active', 'created_by', 'updated_by')
                    ->withTimestamps();
    }

    /**
     * Get the user who created this role.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this role.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get active users with this role.
     *
     * @return BelongsToMany
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', 1);
    }

    /**
     * Assign this role to a user.
     *
     * @param User|int $user
     * @return void
     */
    public function assignToUser($user): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        if (!$this->users()->where('user_id', $userId)->exists()) {
            $this->users()->attach($userId, [
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);
        }
    }

    /**
     * Remove this role from a user.
     *
     * @param User|int $user
     * @return void
     */
    public function removeFromUser($user): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        $this->users()->detach($userId);
    }

    /**
     * Scope a query to only include active roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
