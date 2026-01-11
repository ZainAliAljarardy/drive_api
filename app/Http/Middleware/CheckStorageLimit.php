<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStorageLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get file size from request (in bytes)
        $file = $request->file('file');
        
        if ($file) {
            $fileSizeBytes = $file->getSize();
            $fileSizeMB = $fileSizeBytes / (1024 * 1024); // Convert bytes to MB
            
            // Check if adding this file would exceed storage limit
            $totalStorageAfterUpload = $user->used_storage + $fileSizeMB;
            
            if ($totalStorageAfterUpload > $user->storage_limit) {
                return response()->json([
                    'message' => 'Storage Full'
                ], 403);
            }
        }

        return $next($request);
    }
}
