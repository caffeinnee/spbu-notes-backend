<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'email'         => 'required|string|email|unique:users',
            'password'      => 'required|string|min:6',
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|max:20',
            'kota_asal'     => 'required|string|max:255',
            'nomor_hp'      => 'required|string|max:20',
        ]);

        $user = User::create([
            'name'          => $fields['nama_lengkap'],
            'email'         => $fields['email'],
            'password'      => bcrypt($fields['password']),
            'nama_lengkap'  => $fields['nama_lengkap'],
            'jenis_kelamin' => $fields['jenis_kelamin'],
            'kota_asal'     => $fields['kota_asal'],
            'nomor_hp'      => $fields['nomor_hp'],
            'role'          => 'KR', // Self-registered users are always Karyawan
        ]);

        $token = $user->createToken('spbu-app-token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (! $user || ! Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Revoke old tokens and issue fresh one
        $user->tokens()->delete();
        $token = $user->createToken('spbu-app-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'user'   => array_merge($user->toArray(), ['role' => $user->role ?? 'KR']),
            'token'  => $token,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $fields = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $request->user()->update([
            'password' => bcrypt($fields['password']),
        ]);

        return response()->json(['status' => true, 'message' => 'Password berhasil diubah.']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['status' => true, 'message' => 'Berhasil logout.']);
    }
}
