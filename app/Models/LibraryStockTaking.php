<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryStockTaking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'library_stock_takings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'started_at',
        'ended_at',
        'status',
        'conducted_by_staff_id',
        'note',
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
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get all stock taking items for this audit.
     */
    public function stockTakingItems()
    {
        return $this->hasMany(LibraryStockTakingItem::class, 'stock_taking_id');
    }

    /**
     * Get the staff who conducted this stock taking.
     */
    public function conductedBy()
    {
        return $this->belongsTo(Staff::class, 'conducted_by_staff_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
