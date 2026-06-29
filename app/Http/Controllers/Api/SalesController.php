<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    protected $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Display a listing of sales transactions.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Sale::with(['lead', 'property']);

        if ($user->role === 'sales_executive') {
            $query->where('sales_officer_id', $user->id);
        }

        $sales = $query->orderBy('deal_closed_at', 'desc')->paginate(15);

        return response()->json($sales);
    }

    /**
     * Record a closed sale transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'property_id' => 'required|exists:properties,id',
            'deal_value' => 'required|numeric|min:0',
            'units_purchased' => 'required|integer|min:1',
            'status' => 'required|string|in:Payment Processing,Closed Won',
        ]);

        $validated['sales_officer_id'] = Auth::id();

        $sale = $this->salesService->recordSale($validated, Auth::id());

        return response()->json([
            'message' => 'Sale recorded successfully',
            'sale' => $sale
        ], 201);
    }
}
