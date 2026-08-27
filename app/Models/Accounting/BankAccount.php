<?php

namespace App\Models\Accounting;

use App\Models\Inventory\InventoryChartOfAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'account_name',
        'bank_name',
        'account_number',
        'currency',
        'opening_balance',
        'current_balance',
        'gl_account_code',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(InventoryChartOfAccount::class, 'gl_account_code', 'account_code');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '₦' . number_format($this->current_balance, 2);
    }
}
