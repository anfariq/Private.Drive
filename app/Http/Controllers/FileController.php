<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Group;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $user = request()->user();

        //jika admin bisa melihat list user
        if ($user->role === 'admin') {
            return File::with('user')->latest()->get();
        }
        // Jika user biasa, hanya tampilkan file yang dia upload (baik yang pribadi maupun yang di grup)
        return File::where('user_id', $user->id)
            ->whereNull('group_id')
            ->latest()
            ->get();

    }
    /**
     * UPLOAD PRIBADI (MY DRIVE)
     */
    public function storePrivate(Request $request)
    {
        $user = $request->user();
        $request->validate(['file' => 'required|file|max:2048000']);

        $file = $request->file('file');
        $fileSize = $file->getSize();

        // Hitung limit pribadi
        $userLimit = $user->storage_limit * 1024 * 1024 * 1024;
        $currentUserStorage = File::where('user_id', $user->id)->whereNull('group_id')->sum('size');

        if ($currentUserStorage + $fileSize > $userLimit) {
            return response()->json(['error' => 'Storage Pribadi Penuh!'], 403);
        }

        $telegramId = $this->uploadToTelegram($file, "Private: {$user->name}");

        $newFile = File::create([
            'user_id' => $user->id,
            'group_id' => null, // TEGAS: Paksa NULL
            'name' => $file->getClientOriginalName(),
            'path' => $telegramId,
            'mime_type' => $file->getMimeType(),
            'size' => $fileSize,
        ]);

        return response()->json($newFile, 201);
    }

    /**
     * UPLOAD GRUP (SHARED DRIVE)
     */
    public function storeGroup(Request $request, $groupId)
    {
        $user = $request->user();
        $request->validate(['file' => 'required|file|max:2048000']);

        $file = $request->file('file');
        $fileSize = $file->getSize();

        // Hitung limit grup (5GB)
        $groupLimit = 5 * 1024 * 1024 * 1024;
        $currentGroupStorage = File::where('group_id', $groupId)->sum('size');

        if ($currentGroupStorage + $fileSize > $groupLimit) {
            return response()->json(['error' => 'Storage Grup (5GB) Penuh!'], 403);
        }

        $telegramId = $this->uploadToTelegram($file, "Group ID: {$groupId}");

        $newFile = File::create([
            'user_id' => $user->id,
            'group_id' => $groupId, // TEGAS: Paksa ID Grup
            'name' => $file->getClientOriginalName(),
            'path' => $telegramId,
            'mime_type' => $file->getMimeType(),
            'size' => $fileSize,
        ]);

        return response()->json($newFile, 201);
    }

    /**
     * HELPER UPLOAD (Biar tidak nulis ulang)
     */
    private function uploadToTelegram($file, $caption)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_FILE_CHID');

        $response = Http::withoutVerifying()
            ->attach('document', file_get_contents($file), $file->getClientOriginalName())
            ->post("https://api.telegram.org/bot{$token}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => $caption
            ]);

        return $response->json()['result']['document']['file_id'];
    }

    public function download(File $file)
    {
        $user = auth()->user();

        // Pastikan user punya akses (Pemilik, Admin, atau Member Grup)
        $isOwner = $user->id === $file->user_id;
        $isAdmin = $user->role === 'admin';
        $isGroupMember = $file->group_id && DB::table('group_user')
            ->where('group_id', $file->group_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isOwner && !$isAdmin && !$isGroupMember) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $token = env('TELEGRAM_BOT_TOKEN');

        // 1. Ambil file_path dari Telegram
        $getFile = Http::get("https://api.telegram.org/bot{$token}/getFile?file_id={$file->path}");

        // CEK DISINI: Jika Telegram gagal kasih path, kirim error yang jelas
        if (!$getFile->successful() || !isset($getFile->json()['result']['file_path'])) {
            return response()->json(['error' => 'File tidak ditemukan di server Telegram'], 404);
        }

        $filePath = $getFile->json()['result']['file_path'];
        $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

        // 2. Stream download ke user
        // Gunakan try-catch agar jika koneksi ke Telegram putus, tidak langsung 500
        try {
            return response()->streamDownload(function () use ($fileUrl) {
                $stream = fopen($fileUrl, 'r');
                while (!feof($stream)) {
                    echo fread($stream, 1024 * 8);
                    flush();
                }
                fclose($stream);
            }, $file->name);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Koneksi ke Telegram bermasalah'], 500);
        }
    }

    public function backupToPrivate(File $file)
    {
        $user = auth()->user();

        // Cek apakah file ini memang file grup
        if (!$file->group_id) {
            return response()->json(['error' => 'File ini sudah ada di Drive pribadi'], 400);
        }

        // Cek apakah user sudah membackup file ini sebelumnya (biar tidak duplikat)
        $exists = File::where('user_id', $user->id)
            ->where('path', $file->path) // file_id telegram yang sama
            ->whereNull('group_id')
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'File sudah dibackup sebelumnya'], 400);
        }

        // Cek kuota storage pribadi user sebelum backup
        $currentUserStorage = File::where('user_id', $user->id)->whereNull('group_id')->sum('size');
        $userLimit = $user->storage_limit * 1024 * 1024 * 1024;

        if ($currentUserStorage + $file->size > $userLimit) {
            return response()->json(['error' => 'Storage pribadi tidak cukup untuk backup'], 403);
        }

        // Clone record file tapi set group_id jadi null dan user_id jadi user yang menekan tombol
        $backup = $file->replicate();
        $backup->user_id = $user->id;
        $backup->group_id = null;
        $backup->save();

        return response()->json(['message' => 'Berhasil backup ke My Drive!', 'file' => $backup]);
    }

    public function destroy(File $file)
    {
        $user = auth()->user();

        // Cek izin hapus
        if ($user->id !== $file->user_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Hapus record ini saja. 
        // Karena hasil backup punya ID berbeda, mereka tidak akan ikut terhapus.
        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
    public function getGroupFiles($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Ambil file yang group_id nya sesuai, sertakan info pengunggah (user)
        $files = File::where('group_id', $groupId)->with('user')->get();

        return response()->json([
            'group_name' => $group->name,
            'files' => $files
        ]);
    }
}
