<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display listing of properties.
     */
    public function index(Request $request)
    {
        $query = Property::query();

        if ($request->filled('type')) {
            $query->where('property_type', $request->type);
        }

        $properties = $query->orderBy('name', 'asc')->paginate(15);

        return response()->json($properties);
    }

    /**
     * Display detailed property.
     */
    public function show(Property $property)
    {
        return response()->json($property);
    }
}
