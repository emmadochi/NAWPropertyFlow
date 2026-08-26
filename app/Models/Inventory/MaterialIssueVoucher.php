<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialIssueVoucher extends Model
{
    use HasFactory;

    protected $table = 'material_issue_vouchers';

    protected $fillable = [
        'ref_number',
        'site_id',
        'issued_by_user_id',
        'received_by_user_id',
        'activity_name',
        'work_quantity',
        'work_unit',
        'bom_expected_quantities',
        'status',
        'foreman_signature_data',
        'storekeeper_signature_data',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'work_quantity' => 'decimal:3',
        'bom_expected_quantities' => 'array',
        'issued_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MivItem::class, 'miv_id');
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class, 'miv_id');
    }
}
