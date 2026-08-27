<?php

namespace App\Services\Accounting;

use App\Models\Inventory\SupplierInvoice;
use App\Models\PaymentMilestone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgingAnalysisService
{
    /**
     * Accounts Receivable (AR) Aging Analysis (Property Buyer Installments)
     * Buckets: Current (0-30 days), 31-60 days, 61-90 days, 90+ days overdue
     */
    public function getAccountsReceivableAging(): array
    {
        $milestones = PaymentMilestone::with(['paymentPlan.sale.lead', 'paymentPlan.sale.property'])
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->get();

        $buckets = [
            'current' => ['label' => 'Current (0–30 Days)', 'total' => 0, 'count' => 0, 'items' => []],
            'days_31_60' => ['label' => '31–60 Days Overdue', 'total' => 0, 'count' => 0, 'items' => []],
            'days_61_90' => ['label' => '61–90 Days Overdue', 'total' => 0, 'count' => 0, 'items' => []],
            'over_90' => ['label' => '90+ Days Critical', 'total' => 0, 'count' => 0, 'items' => []],
        ];

        $totalReceivables = 0;
        $now = now()->startOfDay();

        foreach ($milestones as $m) {
            $amount = (float) $m->amount;
            $totalReceivables += $amount;
            $dueDate = Carbon::parse($m->due_date)->startOfDay();
            $daysOverdue = $dueDate->isPast() ? $dueDate->diffInDays($now) : 0;

            $item = [
                'milestone_id' => $m->id,
                'title' => $m->title,
                'customer_name' => $m->paymentPlan?->sale?->lead?->name ?? 'Buyer',
                'customer_phone' => $m->paymentPlan?->sale?->lead?->phone ?? 'N/A',
                'property_title' => $m->paymentPlan?->sale?->property?->title ?? 'Unit',
                'amount' => $amount,
                'due_date' => $m->due_date->toDateString(),
                'days_overdue' => $daysOverdue,
                'status' => $m->status,
            ];

            if ($daysOverdue <= 30) {
                $buckets['current']['total'] += $amount;
                $buckets['current']['count']++;
                $buckets['current']['items'][] = $item;
            } elseif ($daysOverdue <= 60) {
                $buckets['days_31_60']['total'] += $amount;
                $buckets['days_31_60']['count']++;
                $buckets['days_31_60']['items'][] = $item;
            } elseif ($daysOverdue <= 90) {
                $buckets['days_61_90']['total'] += $amount;
                $buckets['days_61_90']['count']++;
                $buckets['days_61_90']['items'][] = $item;
            } else {
                $buckets['over_90']['total'] += $amount;
                $buckets['over_90']['count']++;
                $buckets['over_90']['items'][] = $item;
            }
        }

        return [
            'total_receivables' => $totalReceivables,
            'buckets' => $buckets,
        ];
    }

    /**
     * Accounts Payable (AP) Aging Analysis (Supplier & Contractor Invoices)
     */
    public function getAccountsPayableAging(): array
    {
        $invoices = SupplierInvoice::with(['supplier', 'purchaseOrder.site'])
            ->whereIn('payment_status', ['unmatched', 'matched', 'disputed', 'approved_for_payment'])
            ->orderBy('due_date', 'asc')
            ->get();

        $buckets = [
            'current' => ['label' => 'Current (0–30 Days)', 'total' => 0, 'count' => 0, 'items' => []],
            'days_31_60' => ['label' => '31–60 Days Outstanding', 'total' => 0, 'count' => 0, 'items' => []],
            'days_61_90' => ['label' => '61–90 Days Outstanding', 'total' => 0, 'count' => 0, 'items' => []],
            'over_90' => ['label' => '90+ Days Critical AP', 'total' => 0, 'count' => 0, 'items' => []],
        ];

        $totalPayables = 0;
        $now = now()->startOfDay();

        foreach ($invoices as $inv) {
            $amount = (float) $inv->total_amount;
            $totalPayables += $amount;
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date)->startOfDay() : Carbon::parse($inv->invoice_date)->addDays(30)->startOfDay();
            $daysOverdue = $dueDate->isPast() ? $dueDate->diffInDays($now) : 0;

            $item = [
                'invoice_id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'supplier_name' => $inv->supplier?->name ?? 'Vendor',
                'po_ref' => $inv->purchaseOrder?->ref_number ?? 'N/A',
                'site_name' => $inv->purchaseOrder?->site?->name ?? 'HQ',
                'amount' => $amount,
                'due_date' => $dueDate->toDateString(),
                'days_overdue' => $daysOverdue,
                'payment_status' => $inv->payment_status,
            ];

            if ($daysOverdue <= 30) {
                $buckets['current']['total'] += $amount;
                $buckets['current']['count']++;
                $buckets['current']['items'][] = $item;
            } elseif ($daysOverdue <= 60) {
                $buckets['days_31_60']['total'] += $amount;
                $buckets['days_31_60']['count']++;
                $buckets['days_31_60']['items'][] = $item;
            } elseif ($daysOverdue <= 90) {
                $buckets['days_61_90']['total'] += $amount;
                $buckets['days_61_90']['count']++;
                $buckets['days_61_90']['items'][] = $item;
            } else {
                $buckets['over_90']['total'] += $amount;
                $buckets['over_90']['count']++;
                $buckets['over_90']['items'][] = $item;
            }
        }

        return [
            'total_payables' => $totalPayables,
            'buckets' => $buckets,
        ];
    }
}
