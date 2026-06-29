<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowUp;
use App\Models\Inspection;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getAlerts()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'alerts' => []]);
        }

        $officerId = $user->role === 'sales_executive' ? $user->id : null;
        $alerts = [];

        // 1. Overdue & Pending Follow-Ups
        $followUpQuery = FollowUp::where('status', 'Pending');
        if ($officerId) {
            $followUpQuery->whereHas('lead', function($q) use ($officerId) {
                $q->where('assigned_to', $officerId);
            });
        } else {
            $followUpQuery->whereHas('lead');
        }
        $followUps = $followUpQuery->with('lead')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        foreach ($followUps as $f) {
            $alerts[] = [
                'id' => 'followup-' . $f->id,
                'type' => 'followup',
                'title' => 'Follow-up Due',
                'description' => ($f->type ?: 'Call') . ' with ' . $f->lead->full_name . ' (' . $f->due_date->diffForHumans() . ')',
                'time' => $f->due_date->diffForHumans(),
                'url' => route('leads.show', $f->lead_id)
            ];
        }

        // 2. Scheduled Inspections (occurring within 24 hours)
        $inspectionQuery = Inspection::where('status', 'Scheduled')
            ->where('inspection_date', '>=', Carbon::now())
            ->where('inspection_date', '<=', Carbon::now()->addHours(24))
            ->whereHas('lead');
        if ($officerId) {
            $inspectionQuery->where('assigned_to', $officerId);
        }
        $inspections = $inspectionQuery->with(['lead', 'property'])
            ->orderBy('inspection_date', 'asc')
            ->limit(5)
            ->get();

        foreach ($inspections as $i) {
            $alerts[] = [
                'id' => 'inspection-' . $i->id,
                'type' => 'inspection',
                'title' => 'Upcoming Tour',
                'description' => 'Inspection at ' . $i->property->name . ' with ' . $i->lead->full_name . ' (' . $i->inspection_date->diffForHumans() . ')',
                'time' => $i->inspection_date->diffForHumans(),
                'url' => route('inspections.index')
            ];
        }

        // 3. New Assigned Leads (created in last 48 hours)
        $leadQuery = Lead::where('status', 'New')
            ->where('created_at', '>=', Carbon::now()->subHours(48));
        if ($officerId) {
            $leadQuery->where('assigned_to', $officerId);
        }
        $leads = $leadQuery->orderBy('created_at', 'desc')->limit(5)->get();

        foreach ($leads as $l) {
            $alerts[] = [
                'id' => 'lead-' . $l->id,
                'type' => 'lead',
                'title' => 'New Lead Assigned',
                'description' => $l->full_name . ' is awaiting response.',
                'time' => $l->created_at->diffForHumans(),
                'url' => route('leads.show', $l->id)
            ];
        }

        return response()->json([
            'unread_count' => count($alerts),
            'alerts' => $alerts
        ]);
    }
}
