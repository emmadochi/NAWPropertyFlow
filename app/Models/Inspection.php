<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'property_id',
        'assigned_to',
        'inspection_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
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

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
