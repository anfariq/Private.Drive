<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Tambahkan ini
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = $request->user();

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,      // <--- TAMBAHKAN INI (WAJIB)
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        // Untuk API, kita hapus tokennya
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout dan token dihapus'
        ]);
    }
}