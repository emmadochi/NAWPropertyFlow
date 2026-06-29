<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'reviewed_by', 'review_period', 'score', 'rating',
        'strengths', 'areas_for_improvement', 'goals_next_period',
        'manager_comments', 'employee_comments', 'status',
    ];

    const RATINGS = [
        'excellent' => 'Excellent',
        'good'      => 'Good',
        'average'   => 'Average',
        'poor'      => 'Poor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
