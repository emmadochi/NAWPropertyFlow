<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Sales Executives can only view their own assigned lead data
        $officerId = $user->role === 'sales_executive' ? $user->id : null;

        $dashboardData = $this->reportService->getDashboardData($officerId);
        
        $onboardingTasks = \App\Models\OnboardingTask::where('user_id', $user->id)->orderBy('id')->get();

        return view('dashboard', array_merge($dashboardData, compact('onboardingTasks')));
    }
}
