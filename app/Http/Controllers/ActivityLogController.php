<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('settings.activity-logs', compact('logs'));
    }
}
