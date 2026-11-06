<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Menyajikan foto pengaduan lewat Laravel, bukan lewat symlink public/storage.
 * Cara ini tetap jalan di hosting yang memblokir symlink (kasus 403 Forbidden)
 * dan sekaligus membatasi foto hanya untuk pihak yang berhak melihatnya.
 */
class FileController extends Controller
{
    public function show(Request $request, string $path)
    {
        $complaint = Complaint::where('photo_path', $path)
            ->orWhere('repair_photo_path', $path)
            ->first();

        abort_unless($complaint, 404);

        $user = $request->user();
        $allowed = $user->isAdmin()
            || ($user->isGuru() && (int) $complaint->reporter_id === (int) $user->id)
            || ($user->isPic() && $user->rooms()->where('id', $complaint->room_id)->exists());

        abort_unless($allowed, 403);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
