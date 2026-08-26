<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryJournalItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_journal_items';

    protected $fillable = [
        'journal_entry_id',
        'account_code',
        'entry_type',
        'amount',
        'narration',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(InventoryJournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(InventoryChartOfAccount::class, 'account_code', 'account_code');
    }
}
