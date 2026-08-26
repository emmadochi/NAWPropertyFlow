<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'inventory_chart_of_accounts';

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function journalItems(): HasMany
    {
        return $this->hasMany(InventoryJournalItem::class, 'account_code', 'account_code');
    }
}
