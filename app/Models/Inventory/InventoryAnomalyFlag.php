<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryAnomalyFlag extends Model
{
    use HasFactory;

    protected $table = 'inventory_anomaly_flags';

    protected $fillable = [
        'flag_type',
        'flaggable_type',
        'flaggable_id',
        'site_id',
        'title',
        'description',
        'severity',
        'status',
        'resolved_by_user_id',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function flaggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
