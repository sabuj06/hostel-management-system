<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'asset_category_id', 'name', 'sku', 'quantity_total', 'quantity_available',
        'low_stock_threshold', 'unit_cost', 'purchase_date', 'warranty_expiry',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function roomAssignments(): HasMany
    {
        return $this->hasMany(AssetRoomAssignment::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(AssetDamageReport::class);
    }

    public function isLowStock(): bool
    {
        return $this->low_stock_threshold > 0 && $this->quantity_available <= $this->low_stock_threshold;
    }
}