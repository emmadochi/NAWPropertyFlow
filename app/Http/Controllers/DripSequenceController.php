<?php

namespace App\Http\Controllers;

use App\Models\DripSequence;
use App\Models\DripStep;
use App\Services\DripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DripSequenceController extends Controller
{
    protected $dripService;

    public function __construct(DripService $dripService)
    {
        $this->dripService = $dripService;
    }

    public function index()
    {
        $sequences = DripSequence::with('creator')
            ->withCount(['steps', 'enrollments'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('drip.index', compact('sequences'));
    }

    public function create()
    {
        $triggers = DripSequence::TRIGGERS;
        return view('drip.create', compact('triggers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'trigger_event' => 'required|string|max:255',
        ]);

        $sequence = DripSequence::create([
            'name'          => $validated['name'],
            'description'   => $validated['description'],
            'trigger_event' => $validated['trigger_event'],
            'is_active'     => true,
            'created_by'    => Auth::id() ?? 1,
        ]);

        return redirect()->route('drip-sequences.show', $sequence)
            ->with('success', 'Drip sequence created. Now add steps to your sequence.');
    }

    public function show(DripSequence $dripSequence)
    {
        $dripSequence->load(['steps', 'creator', 'enrollments.lead']);
        return view('drip.show', [
            'sequence' => $dripSequence,
            'steps' => $dripSequence->steps,
        ]);
    }

    public function toggle(DripSequence $dripSequence)
    {
        $dripSequence->update([
            'is_active' => !$dripSequence->is_active,
        ]);

        $status = $dripSequence->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Drip sequence {$status} successfully.");
    }

    public function addStep(Request $request, DripSequence $dripSequence)
    {
        $validated = $request->validate([
            'type'        => 'required|in:email,sms,whatsapp',
            'subject'     => 'required_if:type,email|nullable|string|max:255',
            'body'        => 'required|string',
            'delay_days'  => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
        ]);

        // Get the next step order
        $nextOrder = $dripSequence->steps()->max('step_order') + 1;

        $dripSequence->steps()->create([
            'step_order'  => $nextOrder,
            'type'        => $validated['type'],
            'subject'     => $validated['subject'] ?? null,
            'body'        => $validated['body'],
            'delay_days'  => $validated['delay_days'],
            'delay_hours' => $validated['delay_hours'],
            'is_active'   => true,
        ]);

        return redirect()->route('drip-sequences.show', $dripSequence)
            ->with('success', 'Step added to drip sequence.');
    }

    public function updateStep(Request $request, DripSequence $dripSequence, DripStep $dripStep)
    {
        $validated = $request->validate([
            'type'        => 'required|in:email,sms,whatsapp',
            'subject'     => 'required_if:type,email|nullable|string|max:255',
            'body'        => 'required|string',
            'delay_days'  => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
        ]);

        $dripStep->update([
            'type'        => $validated['type'],
            'subject'     => $validated['subject'] ?? null,
            'body'        => $validated['body'],
            'delay_days'  => $validated['delay_days'],
            'delay_hours' => $validated['delay_hours'],
        ]);

        return redirect()->route('drip-sequences.show', $dripSequence)
            ->with('success', 'Step updated successfully.');
    }

    public function deleteStep(DripSequence $dripSequence, DripStep $dripStep)
    {
        $dripStep->delete();

        // Re-order remaining steps
        $dripSequence->steps()->each(function ($step, $index) {
            $step->update(['step_order' => $index + 1]);
        });

        return redirect()->route('drip-sequences.show', $dripSequence)
            ->with('success', 'Step removed.');
    }

    public function destroy(DripSequence $dripSequence)
    {
        $dripSequence->delete();
        return redirect()->route('drip-sequences.index')
            ->with('success', 'Drip sequence deleted successfully.');
    }
}
