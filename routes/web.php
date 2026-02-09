<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StorageRequestController;
use Illuminate\Support\Facades\Route;


// --- مسارات عامة (لا تحتاج حماية) ---
Route::get('/', function () {
    return view('auth.admin-login');
})->name('login'); // إضافة اسم للرابط لسهولة التوجيه

Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login');


// --- مسارات تحتاج حماية (يجب تسجيل الدخول كأدمن) ---
Route::middleware(['auth'])->group(function () {
    
    // 1. لوحة التحكم (عرض المستخدمين)
    Route::get('/admin/dashboard', [AdminController::class, 'users'])->name('admin.dashboard');

    // 2. تحديث البيانات (يجب أن يكون محمي لأنه يتعامل مع الجلسة)
    Route::post('/admin/refresh', [AdminController::class, 'refresh'])->name('admin.refresh');

    // 3. حظر المستخدمين (عملية حساسة جداً)
    Route::post('/admin/users/{id}/toggle-block', [AdminController::class, 'toggleBlock'])->name('admin.toggle-block');

    // 4. معالجة طلبات التخزين
    Route::post('/admin/storage-requests/{id}/action', [StorageRequestController::class, 'action'])
         ->name('admin.process-request');
});

Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
