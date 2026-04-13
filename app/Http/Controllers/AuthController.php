<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

/**
 * Handle Admin Registration logic.
 */
public function adminRegister(Request $request)
{
    // 1. التحقق من البيانات
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed', // تأكد من وجود حقل password_confirmation في الـ Blade
    ]);

    // 2. إنشاء الأدمن
    $admin = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'role' => 'admin', // تعيين الرتبة كأدمن تلقائياً
        'storage_limit' => 10000, // مثلاً الأدمن يحصل على مساحة أكبر افتراضياً
        'used_storage' => 0,
        'is_blocked' => false,
    ]);

    return redirect()->route('login')->with('success', 'Admin account created successfully. Please login.');
}

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'storage_limit' => 10,
            'used_storage' => 0,
            'is_blocked' => false,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful'
        ], 201);
    }

    /**
     * Login for regular users.
     */
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->role !== 'user') {
            return response()->json([
                'message' => 'Unauthorized. This endpoint is for users only.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    /**
     * Login for admins.
     */
  public function adminLogin(Request $request)
{
    // Validate input
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Attempt login
    if (Auth::attempt($credentials)) {
        // Regenerate session to prevent fixation
        $request->session()->regenerate();

        $user = Auth::user();

        // Check admin role
        if ($user->role === 'admin') {
            // Store user in session
            $request->session()->put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Optional: only if you actually have api_token
            if (!empty($user->api_token)) {
                $request->session()->put('api_token', $user->api_token);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Welcome to Dashboard');
        }

        // Not admin—logout and error
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withErrors([
            'email' => 'Unauthorized. This area is for admins only.',
        ]);
    }

    // Invalid credentials
    throw ValidationException::withMessages([
        'email' => ['The provided credentials are incorrect.'],
    ]);
}

public function getProfile(Request $request)
{
    // 1. جلب المستخدم الحالي من التوكن
    $user = $request->user();

    // 2. إعادة تحميل البيانات من الداتابيز لضمان جلب آخر التحديثات 
    // (مثل الحظر، تغيير المساحة، أو تغيير الاسم)
    $freshUser = \App\Models\User::findOrFail($user->id);

    return response()->json([
        'status' => 'success',
        'user' => $freshUser
    ]);
}
}