<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /** GET /api/notes — all notes for the authenticated user */
    public function index(Request $request)
    {
        $notes = $request->user()
            ->notes()
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $notes,
        ]);
    }

    /** POST /api/notes — create a new note */
    public function store(Request $request)
    {
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

    /** PUT /api/notes/{id} — update an existing note */
    public function update(Request $request, $id)
    {
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

    /** DELETE /api/notes/{id} */
    public function destroy(Request $request, $id)
    {
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
}
