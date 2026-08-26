<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GoodsReceivedNote extends Model
{
    use HasFactory;

    protected $table = 'goods_received_notes';

    protected $fillable = [
        'ref_number',
        'purchase_order_id',
        'site_id',
        'received_by_user_id',
        'delivery_date',
        'delivery_time',
        'waybill_number',
        'driver_name',
        'driver_phone',
        'vehicle_plate',
        'delivery_gps_lat',
        'delivery_gps_lng',
        'geofence_check_passed',
        'photo_evidence_paths',
        'status',
        'remarks',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_gps_lat' => 'decimal:7',
        'delivery_gps_lng' => 'decimal:7',
        'geofence_check_passed' => 'boolean',
        'photo_evidence_paths' => 'array',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(SupplierInvoice::class, 'goods_received_note_id');
    }

    public function supplierInvoice(): HasOne
    {
        return $this->hasOne(SupplierInvoice::class, 'goods_received_note_id');
    }
}
