<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class PropertyUnit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id', 'project_id', 'unit_number', 'unit_type',
        'floor_number', 'size_sqm', 'price', 'service_charge',
        'status', 'description', 'features', 'images',
        'reserved_by_lead_id', 'reserved_at', 'reservation_expires_at', 'reservation_notes',
    ];

    protected $casts = [
        'features'                => 'array',
        'images'                  => 'array',
        'price'                   => 'decimal:2',
        'service_charge'          => 'decimal:2',
        'size_sqm'                => 'decimal:2',
        'reserved_at'             => 'datetime',
        'reservation_expires_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reservedByLead()
    {
        return $this->belongsTo(Lead::class, 'reserved_by_lead_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'property_unit_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function reserve(Lead $lead, int $daysHold = 7, string $notes = null): void
    {
        $this->update([
            'status'                  => 'reserved',
            'reserved_by_lead_id'     => $lead->id,
            'reserved_at'             => now(),
            'reservation_expires_at'  => now()->addDays($daysHold),
            'reservation_notes'       => $notes,
        ]);
    }

    public function release(): void
    {
        $this->update([
            'status'                  => 'available',
            'reserved_by_lead_id'     => null,
            'reserved_at'             => null,
            'reservation_expires_at'  => null,
            'reservation_notes'       => null,
        ]);
    }

    public function markSold(): void
    {
        $this->update(['status' => 'sold']);

        // Update parent property available_units count
        $availableCount = $this->property->units()->available()->count();
        $this->property->update(['available_units' => $availableCount]);
    }

    public function isReservationExpired(): bool
    {
        return $this->status === 'reserved'
            && $this->reservation_expires_at
            && $this->reservation_expires_at->isPast();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'success',
            'reserved'    => 'warning',
            'sold'        => 'danger',
            'unavailable' => 'secondary',
            default       => 'secondary',
        };
    }
}
