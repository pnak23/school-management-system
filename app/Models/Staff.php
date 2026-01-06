<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $fillable = [
        'user_id',
        'khmer_name',
        'english_name',
        'dob',
        'sex',
        'staff_code',
        'photo',
        'birthplace_province_id',
        'birthplace_district_id',
        'birthplace_commune_id',
        'birthplace_village_id',
        'current_province_id',
        'current_district_id',
        'current_commune_id',
        'current_village_id',
        'department_id',
        'position_id',
        'employment_type_id',
        'created_by',
        'updated_by',
        'is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(StaffPhone::class);
    }

    // Location relationships - Birthplace
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

    // Location relationships - Current Address
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
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
}

