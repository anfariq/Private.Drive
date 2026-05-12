<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth:sanctum', 'verified.user'])->group(function () {

    Route::get('/groups', [AdminController::class, 'index']);
    Route::post('/public/groups', [AdminController::class, 'createGroup']);
    Route::delete('/groups/{group}', [AdminController::class, 'deleteGroup']);
    Route::get('/groups/{group}/files', [FileController::class, 'getGroupFiles']);
    Route::post('/groups/{group}/members', [AdminController::class, 'addMember']);

    // manajemen file
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/files/upload/private', [FileController::class, 'storePrivate']);
    Route::post('/files/upload/group/{groupId}', [FileController::class, 'storeGroup']);
    Route::get('/files/download/{file}', [FileController::class, 'download']);
    Route::post('/files/{file}/backup', [FileController::class, 'backupToPrivate']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);

    // info kouta user
    Route::get('/user-storage', function (Request $request) {
        $user = $request->user();

        // 1. Hitung total byte dari DB
        $usedBytes = $user->files()->sum('size') ?? 0;

        // 2. Konversi ke Megabyte (MB)
        $usedMB = round($usedBytes / (1024 * 1024), 2);

        // 3. Konversi Limit (Asumsi di DB isinya 20 untuk 20GB) ke MB
        $limitMB = (float) $user->storage_limit * 1024;

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'used' => $usedMB,      // Hasilnya misal: 0.5 (artinya 0.5 MB)
            'limit' => $limitMB,     // Hasilnya misal: 20480 (artinya 20 GB)
            'role' => $user->role,
        ];
    });

    // admin routes
    Route::middleware('can:admin-only')->group(function () {
        Route::put('//admin/users/{id}/verify', [AdminController::class, 'toggleVerification']);
        Route::get('/admin/users', [AdminController::class, 'listUser']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::put('/admin/users/{id}/storage', [AdminController::class, 'updateStorage']);
    });
});
