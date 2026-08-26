<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteLog extends Model
{
    use HasFactory;

    protected $table = 'waste_logs';

    protected $fillable = [
        'site_id',
        'material_id',
        'miv_id',
        'qty',
        'waste_type',
        'activity_name',
        'responsible_team',
        'description',
        'photo_paths',
        'weather_condition',
        'insurance_claim_raised',
        'logged_by_user_id',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'photo_paths' => 'array',
        'insurance_claim_raised' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }

    public function issueVoucher(): BelongsTo
    {
        return $this->belongsTo(MaterialIssueVoucher::class, 'miv_id');
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }
}
