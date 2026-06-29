<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\File;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FileStorageController extends Controller
{
    public function index($folderId = null)
    {
        $currentFolder = null;
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = Folder::findOrFail($folderId);
            
            // Build breadcrumbs
            $temp = $currentFolder;
            while ($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }
        }

        $folders = Folder::where('parent_id', $folderId)
            ->orderBy('name')
            ->get();

        $files = File::where('folder_id', $folderId)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            return response()->json([
                'currentFolder' => $currentFolder,
                'breadcrumbs' => $breadcrumbs,
                'folders' => $folders,
                'files' => $files,
            ]);
        }

        return view('file-storage.index', compact('currentFolder', 'breadcrumbs', 'folders', 'files'));
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
            'parent_id' => 'nullable|exists:folders,id'
        ], [
            'name.unique' => 'A folder with this name already exists here.'
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'created_by' => Auth::id(),
        ]);

        ActivityLog::create([
            'log_name' => 'file_manager',
            'description' => Auth::user()->name . ' created folder "' . $folder->name . '"',
            'causer_type' => User::class,
            'causer_id' => Auth::id(),
            'subject_type' => Folder::class,
            'subject_id' => $folder->id,
        ]);

        return response()->json([
            'success' => true,
            'folder' => $folder
        ]);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB Max
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        // Unique private path storage
        $name = time() . '_' . uniqid() . '.' . $extension;
        $path = $uploadedFile->storeAs('file_storage', $name, 'local');

        $file = File::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'path' => $path,
            'size' => $size,
            'mime_type' => $mimeType,
            'extension' => strtolower($extension),
            'folder_id' => $request->folder_id,
            'uploaded_by' => Auth::id(),
        ]);

        ActivityLog::create([
            'log_name' => 'file_manager',
            'description' => Auth::user()->name . ' uploaded file "' . $originalName . '"',
            'causer_type' => User::class,
            'causer_id' => Auth::id(),
            'subject_type' => File::class,
            'subject_id' => $file->id,
        ]);

        return response()->json([
            'success' => true,
            'file' => $file
        ]);
    }

    public function download(File $file)
    {
        if (!Storage::disk('local')->exists($file->path)) {
            abort(404, 'Physical file not found on storage.');
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function preview(File $file)
    {
        if (!Storage::disk('local')->exists($file->path)) {
            abort(404, 'Physical file not found on storage.');
        }

        $path = storage_path('app/' . $file->path);
        return response()->file($path, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"'
        ]);
    }

    public function renameFile(Request $request, File $file)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $oldName = $file->original_name;
        
        // Retain extension if they didn't supply it
        $newName = $request->name;
        $ext = pathinfo($oldName, PATHINFO_EXTENSION);
        if ($ext && !pathinfo($newName, PATHINFO_EXTENSION)) {
            $newName .= '.' . $ext;
        }

        $file->original_name = $newName;
        $file->name = pathinfo($newName, PATHINFO_FILENAME);
        $file->save();

        ActivityLog::create([
            'log_name' => 'file_manager',
            'description' => Auth::user()->name . ' renamed file "' . $oldName . '" to "' . $file->original_name . '"',
            'causer_type' => User::class,
            'causer_id' => Auth::id(),
            'subject_type' => File::class,
            'subject_id' => $file->id,
        ]);

        return response()->json(['success' => true, 'file' => $file]);
    }

    public function renameFolder(Request $request, Folder $folder)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders')->where(function ($query) use ($folder) {
                    return $query->where('parent_id', $folder->parent_id);
                })->ignore($folder->id),
            ]
        ]);

        $oldName = $folder->name;
        $folder->name = $request->name;
        $folder->save();

        ActivityLog::create([
            'log_name' => 'file_manager',
            'description' => Auth::user()->name . ' renamed folder "' . $oldName . '" to "' . $folder->name . '"',
            'causer_type' => User::class,
            'causer_id' => Auth::id(),
            'subject_type' => Folder::class,
            'subject_id' => $folder->id,
        ]);

        return response()->json(['success' => true, 'folder' => $folder]);
    }

    public function destroyFile(File $file)
    {
        Storage::disk('local')->delete($file->path);
        $fileName = $file->original_name;
        $file->delete();

        ActivityLog::create([
            'log_name' => 'file_manager',
            'description' => Auth::user()->name . ' deleted file "' . $fileName . '"',
            'causer_type' => User::class,
            'causer_id' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroyFolder(Folder $folder)
    {
        $this->deleteFolderRecursively($folder);

        ActivityLog::create([
            'log_name' => 'file_manager',
            'description' => Auth::user()->name . ' deleted folder "' . $folder->name . '" and all its contents',
            'causer_type' => User::class,
            'causer_id' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    private function deleteFolderRecursively(Folder $folder)
    {
        // Delete all files inside
        foreach ($folder->files as $file) {
            Storage::disk('local')->delete($file->path);
            $file->delete();
        }

        // Recursively delete subfolders
        foreach ($folder->children as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
        }

        $folder->delete();
    }
}
