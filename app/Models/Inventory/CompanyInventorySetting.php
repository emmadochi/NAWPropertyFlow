<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInventorySetting extends Model
{
    use HasFactory;

    protected $table = 'company_inventory_settings';

    protected $attributes = [
        'company_id' => 1,
        'po_tier1_max' => 500000.00,
        'po_tier2_max' => 5000000.00,
        'grn_geofence_strict' => true,
        'after_hours_start' => '18:00:00',
        'after_hours_end' => '07:00:00',
        'waste_alert_multiplier' => 1.5,
        'cement_shelf_life_days' => 90,
        'perfect_match_consecutive_limit' => 3,
        'staff_pairing_occurrences_limit' => 5,
        'price_variance_alert_pct' => 10.00,
    ];

    protected $fillable = [
        'company_id',
        'po_tier1_max',
        'po_tier2_max',
        'grn_geofence_strict',
        'after_hours_start',
        'after_hours_end',
        'waste_alert_multiplier',
        'cement_shelf_life_days',
        'perfect_match_consecutive_limit',
        'staff_pairing_occurrences_limit',
        'price_variance_alert_pct',
    ];

    protected $casts = [
        'po_tier1_max' => 'decimal:2',
        'po_tier2_max' => 'decimal:2',
        'grn_geofence_strict' => 'boolean',
        'waste_alert_multiplier' => 'decimal:2',
        'cement_shelf_life_days' => 'integer',
        'perfect_match_consecutive_limit' => 'integer',
        'staff_pairing_occurrences_limit' => 'integer',
        'price_variance_alert_pct' => 'decimal:2',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['company_id' => 1]);
    }
}
