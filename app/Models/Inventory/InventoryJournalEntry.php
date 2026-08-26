<?php

namespace App\Models\Inventory;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryJournalEntry extends Model
{
    use HasFactory;

    protected $table = 'inventory_journal_entries';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'site_id',
        'project_id',
        'description',
        'total_debit',
        'total_credit',
        'is_balanced',
        'posted_by_user_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_balanced' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryJournalItem::class, 'journal_entry_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
