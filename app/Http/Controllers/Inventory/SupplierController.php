<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount(['purchaseOrders', 'invoices', 'users']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'blacklisted') {
                $query->where('is_blacklisted', true);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true)->where('is_blacklisted', false);
            }
        }

        $suppliers = $query->orderBy('name')->paginate(15);

        return view('inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('inventory.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:suppliers,code',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'rc_number' => 'nullable|string|max:50',
            'tin' => 'nullable|string|max:50',
            'payment_terms_days' => 'required|integer|min:0|max:180',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:20',
            'bank_account_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',

            // Optional Portal Account creation
            'create_portal_user' => 'nullable|boolean',
            'portal_user_name' => 'required_if:create_portal_user,1|nullable|string|max:255',
            'portal_user_email' => 'required_if:create_portal_user,1|nullable|email|unique:supplier_users,email',
            'portal_user_password' => 'required_if:create_portal_user,1|nullable|string|min:8',
        ]);

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'rc_number' => $validated['rc_number'] ?? null,
            'tin' => $validated['tin'] ?? null,
            'payment_terms_days' => $validated['payment_terms_days'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->boolean('create_portal_user')) {
            SupplierUser::create([
                'supplier_id' => $supplier->id,
                'name' => $validated['portal_user_name'],
                'email' => $validated['portal_user_email'],
                'password' => Hash::make($validated['portal_user_password']),
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('inventory.suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->name}' registered successfully.");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'users',
            'purchaseOrders' => function ($q) {
                $q->latest()->take(10);
            },
            'invoices' => function ($q) {
                $q->latest()->take(10);
            },
        ]);

        $totalOrdered = $supplier->purchaseOrders()->sum('total_amount');
        $totalInvoiced = $supplier->invoices()->sum('total_amount');
        $totalPaid = $supplier->invoices()->where('payment_status', 'paid')->sum('total_amount');

        return view('inventory.suppliers.show', compact('supplier', 'totalOrdered', 'totalInvoiced', 'totalPaid'));
    }

    public function edit(Supplier $supplier)
    {
        return view('inventory.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'rc_number' => 'nullable|string|max:50',
            'tin' => 'nullable|string|max:50',
            'payment_terms_days' => 'required|integer|min:0|max:180',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:20',
            'bank_account_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return redirect()->route('inventory.suppliers.show', $supplier)
            ->with('success', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function toggleBlacklist(Request $request, Supplier $supplier)
    {
        $request->validate([
            'blacklist_reason' => 'required_if:action,blacklist|nullable|string',
            'action' => 'required|in:blacklist,unblacklist',
        ]);

        if ($request->action === 'blacklist') {
            $supplier->update([
                'is_blacklisted' => true,
                'blacklist_reason' => $request->blacklist_reason,
            ]);
            $msg = "Supplier '{$supplier->name}' has been blacklisted.";
        } else {
            $supplier->update([
                'is_blacklisted' => false,
                'blacklist_reason' => null,
            ]);
            $msg = "Supplier '{$supplier->name}' restored to active status.";
        }

        return back()->with('success', $msg);
    }
}
