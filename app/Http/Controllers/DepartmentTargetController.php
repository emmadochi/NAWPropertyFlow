<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentTarget;
use Illuminate\Http\Request;

class DepartmentTargetController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $user = \Illuminate\Support\Facades\Auth::user();

        $query = Department::where('is_active', true)
            ->with(['metrics' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name', 'asc');

        if (!$user->hasRole(['super_admin', 'company_admin', 'hr'])) {
            $query->where('hod_id', $user->id);
            if ($query->count() === 0) {
                abort(403, 'You are not assigned as Head of Department for any active business unit.');
            }
        }

        $departments = $query->get();

        $targets = DepartmentTarget::where('target_month', $month)
            ->where('target_year', $year)
            ->get()
            ->groupBy('department_id');

        return view('hr.department-targets', compact('targets', 'month', 'year', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'department'    => 'nullable|string',
            'target_month'  => 'required|integer|min:1|max:12',
            'target_year'   => 'required|integer|min:2020|max:2050',
            'metric'        => 'required|string',
            'target_value'  => 'required|numeric|min:0',
            'actual_value'  => 'nullable|numeric|min:0',
        ]);

        if (!empty($data['department_id'])) {
            $dept = Department::findOrFail($data['department_id']);
        } elseif (!empty($data['department'])) {
            $dept = Department::where('name', $data['department'])->firstOrFail();
        } else {
            return back()->withErrors(['department_id' => 'The department field is required.']);
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user->hasRole(['super_admin', 'company_admin', 'hr']) && $dept->hod_id !== $user->id) {
            abort(403, 'You are only authorized to set targets for your own department.');
        }

        DepartmentTarget::updateOrCreate(
            [
                'department_id' => $dept->id,
                'target_month'  => $data['target_month'],
                'target_year'   => $data['target_year'],
                'metric'        => $data['metric']
            ],
            [
                'department'    => $dept->name, // legacy fallback
                'target_value'  => $data['target_value'],
                'actual_value'  => $data['actual_value'] ?? null,
            ]
        );

        return back()->with('success', 'Department target saved successfully.');
    }
}
