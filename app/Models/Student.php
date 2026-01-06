<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'khmer_name',
        'english_name',
        'dob',
        'sex',
        'code',
        'note',
        'photo',
        'birthplace_province_id',
        'birthplace_district_id',
        'birthplace_commune_id',
        'birthplace_village_id',
        'current_province_id',
        'current_district_id',
        'current_commune_id',
        'current_village_id',
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
        'dob' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user associated with the student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all phone numbers for the student.
     */
    public function phones(): HasMany
    {
        return $this->hasMany(StudentPhone::class);
    }

    /**
     * Get the primary phone number for the student.
     */
    public function primaryPhone()
    {
        return $this->hasOne(StudentPhone::class)->where('is_primary', 1);
    }

    /**
     * Get the user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Location relationships - Birthplace
     */
    public function birthplaceProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'birthplace_province_id');
    }

    public function birthplaceDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'birthplace_district_id');
    }

    public function birthplaceCommune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'birthplace_commune_id');
    }

    public function birthplaceVillage(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'birthplace_village_id');
    }

    /**
     * Location relationships - Current Address
     */
    public function currentProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'current_province_id');
    }

    public function currentDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'current_district_id');
    }

    public function currentCommune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'current_commune_id');
    }

    public function currentVillage(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'current_village_id');
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include inactive students.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}
