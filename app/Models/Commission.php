<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'user_id',
        'commission_type',
        'rate_percent',
        'calculated_amount',
        'status',
        'approved_by',
        'paid_at',
    ];

    protected $casts = [
        'rate_percent' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
