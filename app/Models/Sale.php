<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Sale extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'lead_id',
        'property_id',
        'property_unit_id',
        'sales_officer_id',
        'deal_value',
        'units_purchased',
        'status',
        'payment_receipt',
        'deal_closed_at',
    ];

    protected $casts = [
        'deal_value' => 'decimal:2',
        'units_purchased' => 'integer',
        'deal_closed_at' => 'datetime',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function salesOfficer()
    {
        return $this->belongsTo(User::class, 'sales_officer_id');
    }

    public function paymentPlan()
    {
        return $this->hasOne(PaymentPlan::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
}
