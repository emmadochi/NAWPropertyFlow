<?php

namespace App\Models\Inventory;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySite extends Model
{
    use HasFactory;

    protected $table = 'inventory_sites';

    protected $fillable = [
        'project_id',
        'name',
        'code',
        'address',
        'gps_lat',
        'gps_lng',
        'geofence_radius_meters',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'gps_lat' => 'decimal:7',
        'gps_lng' => 'decimal:7',
        'geofence_radius_meters' => 'integer',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stock(): HasMany
    {
        return $this->hasMany(SiteStock::class, 'site_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(SiteStock::class, 'site_id');
    }

    public function requisitions(): HasMany
    {
        return $this->hasMany(MaterialRequisition::class, 'site_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'site_id');
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class, 'site_id');
    }

    public function issueVouchers(): HasMany
    {
        return $this->hasMany(MaterialIssueVoucher::class, 'site_id');
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class, 'site_id');
    }
}
