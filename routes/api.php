<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\StorageRequestController;
use App\Http\Middleware\CheckBlockStatus;
use App\Http\Middleware\CheckStorageLimit;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/user/login', [AuthController::class, 'userLogin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // File routes with block status and storage limit checks
    Route::get('/files', [FileController::class, 'index']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/files', [FileController::class, 'store'])
        ->middleware([CheckBlockStatus::class, CheckStorageLimit::class]);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
    Route::delete('/files/{id}', [FileController::class, 'destroy']);

    // Storage request routes (for users)
    Route::post('/storage-requests', [StorageRequestController::class, 'store']);

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/storage-requests', [StorageRequestController::class, 'index']);
        Route::put('/storage-requests/{id}/action', [StorageRequestController::class, 'action']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users/{id}/toggle-block', [AdminController::class, 'toggleBlock']);
    });
});
