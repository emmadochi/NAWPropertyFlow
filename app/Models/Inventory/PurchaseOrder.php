<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'ref_number',
        'requisition_id',
        'site_id',
        'supplier_id',
        'created_by_user_id',
        'status',
        'subtotal_amount',
        'tax_amount',
        'delivery_fee',
        'total_amount',
        'approval_tier',
        'approved_by_user_id',
        'approved_at',
        'expected_delivery_date',
        'expiry_date',
        'terms_and_conditions',
        'rejection_reason',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expected_delivery_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(MaterialRequisition::class, 'requisition_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class, 'purchase_order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(SupplierInvoice::class, 'purchase_order_id');
    }
}
