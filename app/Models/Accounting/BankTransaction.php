<?php

namespace App\Models\Accounting;

use App\Models\Inventory\InventoryJournalEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $table = 'bank_transactions';

    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'type',
        'amount',
        'reference',
        'narration',
        'reconciled',
        'reconciled_at',
        'reconciled_by_user_id',
        'journal_entry_id',
        'matched_entity_type',
        'matched_entity_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by_user_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(InventoryJournalEntry::class, 'journal_entry_id');
    }

    public function matchedEntity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'matched_entity_type', 'matched_entity_id');
    }
}
