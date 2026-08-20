<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'approved_by',
        'property_id',
        'branch_id',
        'title',
        'category',
        'amount',
        'expense_date',
        'status',
        'payment_method',
        'receipt_file',
        'vendor_name',
        'reference_number',
        'notes',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Staff who logged the expense.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accountant / Admin who approved the expense.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Linked Estate / Property.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Linked Branch.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Standard expense categories for real estate developers.
     */
    public static function categories(): array
    {
        return [
            'Site Operations' => '🚜 Site Operations & Diesel',
            'Marketing & Media' => '📢 Marketing, Ads & Media',
            'Legal & Title' => '⚖️ Legal & Title Perfection',
            'Office OPEX' => '🏢 Office Admin & Utilities',
            'Logistics & Inspection' => '🚗 Logistics & Site Tours',
            'Construction & Civil' => '🏗️ Construction & Materials',
            'Agency & Professional Fees' => '💼 Surveyor & Professional Fees',
            'Others' => '📦 Miscellaneous Expenses',
        ];
    }
}
