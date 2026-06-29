<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Project;
use App\Models\File;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->query('query');
        if (empty($term) || strlen($term) < 2) {
            return response()->json([
                'leads' => [],
                'properties' => [],
                'projects' => [],
                'files' => []
            ]);
        }

        // Search Leads (Branch Scoped automatically by global scopes)
        $leads = Lead::search($term)
            ->limit(5)
            ->get()
            ->map(function($lead) {
                return [
                    'id' => $lead->id,
                    'title' => $lead->full_name,
                    'subtitle' => $lead->email ?? $lead->phone_number ?? 'No contact info',
                    'url' => route('leads.show', $lead->id)
                ];
            });

        // Search Properties (Branch Scoped automatically)
        $properties = Property::where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('location', 'like', "%{$term}%")
                  ->orWhere('property_type', 'like', "%{$term}%");
            })
            ->limit(5)
            ->get()
            ->map(function($prop) {
                return [
                    'id' => $prop->id,
                    'title' => $prop->name,
                    'subtitle' => $prop->location . ' · ' . ucfirst($prop->property_type) . ' · $' . number_format($prop->price),
                    'url' => route('properties.show', $prop->id)
                ];
            });

        // Search Projects (Corporate wide)
        $projects = Project::where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('developer', 'like', "%{$term}%")
                  ->orWhere('location', 'like', "%{$term}%");
            })
            ->limit(5)
            ->get()
            ->map(function($proj) {
                return [
                    'id' => $proj->id,
                    'title' => $proj->name,
                    'subtitle' => $proj->developer . ' · ' . $proj->location . ' · ' . $proj->completion_percentage . '% Completed',
                    'url' => route('projects.show', $proj->id)
                ];
            });

        // Search Files (Corporate wide / General)
        $files = File::where('original_name', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->limit(5)
            ->get()
            ->map(function($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->original_name,
                    'subtitle' => strtoupper($file->extension) . ' File · ' . number_format($file->size / 1024, 1) . ' KB',
                    'url' => $file->folder_id ? route('file-storage.index', $file->folder_id) : route('file-storage.index')
                ];
            });

        return response()->json([
            'leads' => $leads,
            'properties' => $properties,
            'projects' => $projects,
            'files' => $files
        ]);
    }
}
