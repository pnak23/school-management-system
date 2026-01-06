<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryStockTakingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'library_stock_taking_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stock_taking_id',
        'copy_id',
        'scan_result',
        'condition_note',
        'scanned_by_staff_id',
        'scanned_at',
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
        'scanned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the stock taking audit this item belongs to.
     */
    public function stockTaking()
    {
        return $this->belongsTo(LibraryStockTaking::class, 'stock_taking_id');
    }

    /**
     * Get the library copy being audited.
     */
    public function copy()
    {
        return $this->belongsTo(LibraryCopy::class, 'copy_id');
    }

    /**
     * Get the staff who scanned this item.
     */
    public function scannedBy()
    {
        return $this->belongsTo(Staff::class, 'scanned_by_staff_id');
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
