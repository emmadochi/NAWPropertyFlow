<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesWeeklyFieldLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'week_number',
        'start_date',
        'end_date',
        'canvassing_locations',
        'office_visits_count',
        'expected_payments_count',
        'expected_payments_notes',
        'observations_recommendations',
        'manager_feedback',
    ];

    protected $casts = [
        'year'                    => 'integer',
        'month'                   => 'integer',
        'week_number'             => 'integer',
        'office_visits_count'     => 'integer',
        'expected_payments_count' => 'integer',
        'start_date'              => 'date',
        'end_date'                => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
