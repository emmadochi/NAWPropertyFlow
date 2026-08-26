<?php

namespace App\Models\Inventory;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomTemplate extends Model
{
    use HasFactory;

    protected $table = 'bom_templates';

    protected $fillable = [
        'project_id',
        'material_id',
        'activity_name',
        'qty_per_unit',
        'unit_of_work',
        'allowable_variance_pct',
        'set_by_user_id',
    ];

    protected $casts = [
        'qty_per_unit' => 'decimal:4',
        'allowable_variance_pct' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }
}
