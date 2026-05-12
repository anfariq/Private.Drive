<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            // Admin melihat semua grup yang ada
            return response()->json(Group::withCount('users')->get());
        }

        // User biasa melihat grup di mana dia menjadi member
        // Gunakan 'get()' di akhir relasi
        $myGroups = $user->groups()->withCount('users')->get();

        return response()->json($myGroups);
    }

    public function createGroup(Request $request)
    {
        $user = $request->user();

        // 1. Cek Limit Berdasarkan Role
        if ($user->role === 'user') {
            // Hitung berapa grup yang sudah dibuat oleh user ini
            $createdGroupsCount = Group::where('creator_id', $user->id)->count();

            if ($createdGroupsCount >= 1) {
                return response()->json([
                    'error' => 'Limit reached. Reguler users can only create 1 group.'
                ], 403);
            }
        }

        // 2. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'nullable|array',
        ]);

        // 3. Buat Grup
        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => $user->id, // Simpan siapa pembuatnya
        ]);

        // 4. Otomatis masukkan pembuat ke dalam grup tersebut
        $group->users()->attach($user->id);

        // 5. Tambahkan member lain jika ada (Plan B kamu)
        if ($request->has('user_ids') && is_array($request->user_ids)) {
            $validIds = array_filter($request->user_ids);
            if (!empty($validIds)) {
                $group->users()->syncWithoutDetaching($validIds);
            }
        }

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group->load('users')->loadCount('users'),
        ], 201);
    }

    public function addMember(Request $request, Group $group)
    {
        $user = auth()->user();

        // Hanya pencipta grup atau Admin yang boleh tambah member
        if ($group->creator_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Only the group creator can add members'], 403);
        }

        $request->validate(['user_id' => 'required|exists:users,id']);
        $group->users()->syncWithoutDetaching([$request->user_id]);

        return response()->json(['message' => 'User added to group']);
    }

    public function listUser()
    {
        // hanya admin yang bisa panggil ini via route
        return User::select('id', 'name', 'email', 'role', 'is_verified', 'storage_limit')->get();
    }

    // app/Http/Controllers/AdminController.php
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user',
            'storage_limit' => 'required|integer' // Dalam GB
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'storage_limit' => $request->storage_limit,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function updateStorage(Request $request, $id)
    {
        // 1. Pastikan hanya admin yang bisa (Proteksi tambahan di level method)
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin only.'], 403);
        }

        // 2. Validasi input (harus angka dan minimal 1GB misalnya)
        $request->validate([
            'storage_limit' => 'required|integer|min:1'
        ]);

        // 3. Cari user dan update
        $user = User::findOrFail($id);
        $user->update([
            'storage_limit' => $request->storage_limit
        ]);

        return response()->json([
            'message' => "Storage limit for {$user->name} updated to {$request->storage_limit}GB",
            'user' => $user
        ]);
    }

    public function deleteGroup(Group $group)
    {
        $user = auth()->user();

        // 1. Proteksi: Hanya Creator grup atau Admin yang bisa hapus
        if ($group->creator_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'error' => 'Unauthorized. Only the owner can delete this group.'
            ], 403);
        }

        // 2. Lepaskan semua member dari grup (hapus di tabel pivot)
        $group->users()->detach();

        // 3. Update file yang ada di grup ini menjadi file pribadi (group_id = null)
        // Agar file tidak ikut terhapus secara permanen
        $group->files()->update(['group_id' => null]);

        // 4. Hapus grup
        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully. Files are now moved to private storage.'
        ]);
    }

    public function toggleVerification(Request $request, $id)
    {
        // Cari user manual biar lebih pasti
        $user = User::findOrFail($id);

        // Update manual tanpa mass assignment
        $user->is_verified = filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN);
        $user->save();

        return response()->json([
            'message' => 'Status updated',
            'is_verified' => $user->is_verified
        ]);
    }
}
