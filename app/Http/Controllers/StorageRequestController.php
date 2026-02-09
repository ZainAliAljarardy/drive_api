<?php

namespace App\Http\Controllers;

use App\Models\StorageRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StorageRequestController extends Controller
{
    /**
     * Create a new storage request (for users).
     */
    public function store(Request $request)
    {
        $request->validate([
            'requested_plan' => 'required|in:100MB,200MB,500MB',
        ]);

        // Check if user has a pending request
        $pendingRequest = StorageRequest::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return response()->json([
                'message' => 'You already have a pending storage request'
            ], 400);
        }

        $storageRequest = StorageRequest::create([
            'user_id' => $request->user()->id,
            'requested_plan' => $request->requested_plan,
            'status' => 'pending',
        ]);

        return response()->json([
            'storage_request' => $storageRequest,
            'message' => 'Storage request created successfully'
        ], 201);
    }

    /**
     * Get all storage requests (for admins).
     */
    public function index(Request $request)
    {
        // Ensure user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $requests = StorageRequest::with(['user', 'admin'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'storage_requests' => $requests
        ]);
    }

    /**
     * Approve or reject a storage request (for admins).
     */public function action(Request $request, $id)
{
    // 1. التحقق من البيانات
    $request->validate([
        'action' => 'required|in:approve,reject',
    ]);

    // 2. التحقق من الصلاحيات (بما أنها صفحة Web، يفضل استخدام middleware 'auth' و 'admin')
    if ($request->user()->role !== 'admin') {
        abort(403, 'Unauthorized. Admin access required.');
    }

    $storageRequest = StorageRequest::findOrFail($id);

    // 3. التحقق من حالة الطلب
    if ($storageRequest->status !== 'pending') {
        return back()->with('error', 'This request has already been processed');
    }

    DB::beginTransaction();
    try {
        $storageRequest->status = $request->action === 'approve' ? 'approved' : 'rejected';
        $storageRequest->admin_id = $request->user()->id;
        $storageRequest->save();

        if ($request->action === 'approve') {
            // استخراج الرقم من الخطة (مثلاً "100MB" تصبح 100)
            $planValue = (int) str_replace('MB', '', $storageRequest->requested_plan);
            
            $user = User::findOrFail($storageRequest->user_id);
            $user->storage_limit = $planValue;
            $user->save();
        }

        DB::commit();

        // الرد لصفحة Blade
        return back()->with('success', 'Storage request ' . $request->action . 'ed successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to process request: ' . $e->getMessage());
    }
}
}
