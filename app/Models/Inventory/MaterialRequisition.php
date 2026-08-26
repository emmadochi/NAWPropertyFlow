<?php

namespace App\Models\Inventory;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaterialRequisition extends Model
{
    use HasFactory;

    protected $table = 'material_requisitions';

    protected $fillable = [
        'ref_number',
        'site_id',
        'project_id',
        'requested_by_user_id',
        'activity_name',
        'required_date',
        'status',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequisitionItem::class, 'requisition_id');
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class, 'requisition_id');
    }
}
