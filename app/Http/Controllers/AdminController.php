<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get all users with their plans and block status (for admins).
     */
    public function users(Request $request)
    {
        // 1. التأكد من أن المستخدم الحالي هو أدمن
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        // 2. تعديل الاستعلام لجلب المستخدمين العاديين فقط
        $users = User::where('role', 'user') // إضافة هذا السطر فقط
            ->withCount('files')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Toggle block status of a user (for admins).
     */
    public function toggleBlock(Request $request, $id)
    {
        // Ensure user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $user = User::findOrFail($id);

        // Prevent admin from blocking themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot block yourself'
            ], 400);
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json([
            'user' => $user,
            'message' => 'User ' . ($user->is_blocked ? 'blocked' : 'unblocked') . ' successfully'
        ]);
    }
}
