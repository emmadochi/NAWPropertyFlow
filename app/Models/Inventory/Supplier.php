<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'address',
        'rc_number',
        'tin',
        'payment_terms_days',
        'performance_score',
        'is_blacklisted',
        'blacklist_reason',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'is_active',
    ];

    protected $casts = [
        'payment_terms_days' => 'integer',
        'performance_score' => 'decimal:2',
        'is_blacklisted' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(SupplierUser::class, 'supplier_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class, 'supplier_id');
    }
}
