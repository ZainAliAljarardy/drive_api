<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\http\Controllers\AuthController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
{
    
    /**
     * Get all users with their plans and block status (for admins).
     */
    public function users(Request $request)
{
    if ($request->user()->role !== 'admin') {
        abort(403, 'Unauthorized. Admin access required.');
    }

    $users = User::where('role', 'user')
        ->withCount('files')
        ->orderBy('created_at', 'desc')
        ->get();

    $pendingRequests = \App\Models\StorageRequest::with('user')
        ->where('status', 'pending')
        ->get();

    return view('admin.dashboard', compact('users', 'pendingRequests'));
}

    /**
     * Toggle block status of a user (for admins).
     */
   public function toggleBlock(Request $request, $id)
{
    // 1. التأكد من الصلاحيات (اختياري إذا كان هناك Middleware)
    if ($request->user()->role !== 'admin') {
        return abort(403, 'Unauthorized action.');
    }

    $user = User::findOrFail($id);

    if ($user->id === $request->user()->id) {
        return back()->with('error', 'You cannot block yourself!');
    }

    $user->is_blocked = !$user->is_blocked;
    $user->save();

    $statusMessage = $user->is_blocked ? 'User has been blocked.' : 'User has been unblocked.';
    
    return back()->with('success', $statusMessage);
}

    /**
     * Refresh user data from API
     */
   public function refresh()
{
    try {
        $user = \App\Models\User::find(auth()->id());

        if ($user) {
            $user->loadCount('files'); 
            session(['user' => $user->toArray()]);
            return back()->with('success', 'تم تحديث البيانات بنجاح');
        }

        return back()->with('error', 'User not found');

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Refresh failed: ' . $e->getMessage()]);
    }
}

/**
     * Logout
     */
    public function logout()
    {
        session()->forget('access_token');
        Session::flush();
        return redirect()->route('login');
    }


}

   