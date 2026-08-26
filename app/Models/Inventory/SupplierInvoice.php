<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoice extends Model
{
    use HasFactory;

    protected $table = 'supplier_invoices';

    protected $fillable = [
        'purchase_order_id',
        'goods_received_note_id',
        'supplier_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'payment_status',
        'matched_by_user_id',
        'matched_at',
        'payment_approved_by_user_id',
        'payment_approved_at',
        'invoice_file_path',
        'discrepancy_notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'matched_at' => 'datetime',
        'payment_approved_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'goods_received_note_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function matcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by_user_id');
    }

    public function paymentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_approved_by_user_id');
    }
}
