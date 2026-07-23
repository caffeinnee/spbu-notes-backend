<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class NoteController extends Controller
{
    /** Helper: check if the authenticated user is Admin/Manajer */
    private function isAdmin(Request $request): bool
    {
        return ($request->user()->role ?? 'KR') === 'AM';
    }

    /** GET /api/notes
     *  - Admin: returns ALL notes (from all users), ordered by updated_at desc
     *  - Karyawan: returns only their own notes
     */
    public function index(Request $request)
    {
        if ($this->isAdmin($request)) {
            // Admin can see all notes with owner info
            $notes = Note::with('user:id,nama_lengkap,email')
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($note) {
                    $arr = $note->toArray();
                    $arr['owner_name'] = $note->user->nama_lengkap ?? $note->user->email ?? '-';
                    return $arr;
                });
        } else {
            $notes = $request->user()
                ->notes()
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return response()->json([
            'status' => true,
            'data'   => $notes,
        ]);
    }

    /** POST /api/notes — only Karyawan can create notes */
    public function store(Request $request)
    {
        if ($this->isAdmin($request)) {
            return response()->json([
                'status'  => false,
                'message' => 'Admin tidak diizinkan membuat catatan.',
            ], 403);
        }

        $fields = $request->validate([
            'judul'         => 'required|string|max:255',
            'isi'           => 'nullable|string',
            'is_pin_locked' => 'boolean',
            'pin_code'      => 'nullable|string|max:6',
            'foto_paths'    => 'nullable|string',
            'dokumen_paths' => 'nullable|string',
        ]);

        $note = $request->user()->notes()->create([
            'judul'         => $fields['judul'],
            'isi'           => $fields['isi'] ?? '',
            'is_pin_locked' => $fields['is_pin_locked'] ?? false,
            'pin_code'      => $fields['pin_code'] ?? null,
            'foto_paths'    => $fields['foto_paths'] ?? '',
            'dokumen_paths' => $fields['dokumen_paths'] ?? '',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Catatan berhasil dibuat.',
            'data'    => $note,
        ], 201);
    }

    /** PUT /api/notes/{id} — only the owner (Karyawan) can update */
    public function update(Request $request, $id)
    {
        if ($this->isAdmin($request)) {
            return response()->json([
                'status'  => false,
                'message' => 'Admin tidak diizinkan mengubah catatan.',
            ], 403);
        }

        // Karyawan can only edit their own notes
        $note = $request->user()->notes()->find($id);

        if (! $note) {
            return response()->json([
                'status'  => false,
                'message' => 'Catatan tidak ditemukan.',
            ], 404);
        }

        $fields = $request->validate([
            'judul'         => 'sometimes|required|string|max:255',
            'isi'           => 'nullable|string',
            'is_pin_locked' => 'boolean',
            'pin_code'      => 'nullable|string|max:6',
            'foto_paths'    => 'nullable|string',
            'dokumen_paths' => 'nullable|string',
        ]);

        $note->update($fields);

        return response()->json([
            'status'  => true,
            'message' => 'Catatan berhasil diperbarui.',
            'data'    => $note->fresh(),
        ]);
    }

    /** DELETE /api/notes/{id} — only the owner (Karyawan) can delete */
    public function destroy(Request $request, $id)
    {
        if ($this->isAdmin($request)) {
            return response()->json([
                'status'  => false,
                'message' => 'Admin tidak diizinkan menghapus catatan.',
            ], 403);
        }

        $note = $request->user()->notes()->find($id);

        if (! $note) {
            return response()->json([
                'status'  => false,
                'message' => 'Catatan tidak ditemukan.',
            ], 404);
        }

        $note->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Catatan berhasil dihapus.',
        ]);
    }

    /** POST /api/notes/{id}/verify-pin
     *  Verifies the PIN for a locked note.
     *  Rate limited to 5 attempts per minute per user+note combo.
     *  Admin always passes (no PIN required).
     */
    public function verifyPin(Request $request, $id)
    {
        $user = $request->user();

        // Admin bypasses PIN entirely
        if (($user->role ?? 'KR') === 'AM') {
            return response()->json(['status' => true, 'message' => 'Admin akses diizinkan.']);
        }

        // Rate limiting: max 5 attempts per minute per user per note
        $key = 'pin-verify:' . $user->id . ':' . $id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'status'  => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $request->validate(['pin' => 'required|string|max:6']);

        // Find note — Karyawan can only verify their own notes
        $note = $user->notes()->find($id);
        if (! $note) {
            return response()->json(['status' => false, 'message' => 'Catatan tidak ditemukan.'], 404);
        }

        if (! $note->is_pin_locked || ! $note->pin_code) {
            return response()->json(['status' => true, 'message' => 'Catatan tidak dikunci.']);
        }

        // Verify PIN (plain text comparison — PIN is stored as-is for now)
        $pinCorrect = ($request->pin === $note->pin_code);

        if (! $pinCorrect) {
            RateLimiter::hit($key, 60); // 60 seconds window
            $remaining = 5 - RateLimiter::attempts($key);
            return response()->json([
                'status'    => false,
                'message'   => 'PIN salah.',
                'remaining' => max(0, $remaining),
            ], 422);
        }

        RateLimiter::clear($key);
        return response()->json(['status' => true, 'message' => 'PIN benar.']);
    }
}
