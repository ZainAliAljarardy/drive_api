<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * List all files for the authenticated user.
     */
    public function index(Request $request)
    {
        $files = File::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'files' => $files
        ]);
    }

    /**
     * Upload a new file.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:512000', // Max 500MB in KB (for 500MB plan)
        ]);

        $user = $request->user();
        $uploadedFile = $request->file('file');
        
        // Get file size in bytes and convert to MB
        $fileSizeBytes = $uploadedFile->getSize();
        $fileSizeMB = $fileSizeBytes / (1024 * 1024);
        
        // Generate unique filename
        $filename = time() . '_' . $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->storeAs('files', $filename, 'public');
        
        // Get file extension
        $extension = $uploadedFile->getClientOriginalExtension();
        
        // Create file record
        $file = File::create([
            'user_id' => $user->id,
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'size' => $fileSizeMB,
            'extension' => $extension,
        ]);
        
        // Update user's used storage
        $user->used_storage += $fileSizeMB;
        $user->save();

        return response()->json([
            'file' => $file,
            'message' => 'File uploaded successfully'
        ], 201);
    }

    /**
     * Download a file.
     */
    public function download(Request $request, $id)
    {
        $file = File::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($file->path)) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Delete a file.
     */
    public function destroy(Request $request, $id)
    {
        $file = File::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Delete physical file
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Update user's used storage
        $user = $request->user();
        $user->used_storage -= $file->size;
        if ($user->used_storage < 0) {
            $user->used_storage = 0;
        }
        $user->save();

        // Delete file record
        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully'
        ]);
    }
}
