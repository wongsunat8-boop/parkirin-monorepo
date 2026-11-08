<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengendara;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Unified login for both User (staff) and Pengendara (customer).
     * Accepts:
     * - { "guard": "user", "username": "...", "password": "..." }
     * - { "guard": "pengendara", "email": "...", "password": "..." }
     * - Auto-detects if "guard" is omitted.
     */
    public function login(Request $request): JsonResponse
    {
        $guard = $request->input('guard');

        if ($guard === 'user') {
            return $this->loginUser($request);
        }

        if ($guard === 'pengendara') {
            return $this->loginPengendara($request);
        }

        // Auto-detect based on input
        $login = $request->input('email') ?? $request->input('username');

        if ($login && filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return $this->loginPengendara($request);
        }

        // Try staff user by username first
        $user = User::where('username', $login)->first();
        if ($user) {
            return $this->loginUser($request);
        }

        return $this->loginPengendara($request);
    }

    /**
     * Login as User (staff).
     */
    private function loginUser(Request $request): JsonResponse
    {
        $username = $request->input('username') ?? $request->input('email');
        $password = $request->input('password');

        if (!$username || !$password) {
            throw ValidationException::withMessages([
                'username' => ['Username dan password wajib diisi.'],
            ]);
        }

        $user = User::with('tempatParkir:id_tempat_parkir,status_aktif')->where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Pengecekan 1: status tenant (terlepas dari status akun) untuk user terikat tenant.
        if ($user->id_tempat_parkir && $user->role !== 'super_admin') {
            $tenantAktif = (int) ($user->tempatParkir->status_aktif ?? 0) === 1;
            if (!$tenantAktif) {
                throw ValidationException::withMessages([
                    'username' => ['Tenant telah dinonaktifkan. Anda tidak dapat masuk.'],
                ]);
            }
        }

        // Pengecekan 2: status akun user.
        if (!(int) $user->status_aktif) {
            throw ValidationException::withMessages([
                'username' => ['User telah dinonaktifkan. Akun Anda tidak dapat digunakan untuk masuk.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'guard'   => 'user',
            'user'    => [
                'id_user'          => $user->id_user,
                'nama_lengkap'     => $user->nama_lengkap,
                'username'         => $user->username,
                'role'             => $user->role,
                'id_tempat_parkir' => $user->id_tempat_parkir,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Login as Pengendara (customer).
     */
    private function loginPengendara(Request $request): JsonResponse
    {
        $login = $request->input('email') ?? $request->input('username');
        $password = $request->input('password');

        if (!$login || !$password) {
            throw ValidationException::withMessages([
                'email' => ['Email atau username dan password wajib diisi.'],
            ]);
        }

        $pengendara = Pengendara::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (!$pengendara || !$pengendara->password || !Hash::check($password, $pengendara->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email/Username atau password salah.'],
            ]);
        }

        if (!$pengendara->status_aktif) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan.'],
            ]);
        }

        $token = $pengendara->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'guard'   => 'pengendara',
            'user'    => [
                'id_pengendara' => $pengendara->id_pengendara,
                'nama_lengkap'  => $pengendara->nama_lengkap,
                'email'         => $pengendara->email,
                'username'      => $pengendara->username,
                'no_telepon'    => $pengendara->no_telepon,
                'skor_reputasi' => $pengendara->skor_reputasi,
            ],
            'token' => $token,
        ]);
    }

    /**
     * POST /api/auth/register
     * Register as Pengendara.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'email'        => 'required|email|max:100|unique:pengendara,email',
            'username'     => 'required|string|max:50|alpha_dash|unique:pengendara,username',
            'no_telepon'   => 'nullable|string|max:15',
            'password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        $pengendara = Pengendara::create([
            'nama_lengkap'  => $validated['nama_lengkap'],
            'email'         => $validated['email'],
            'username'      => $validated['username'],
            'no_telepon'    => $validated['no_telepon'] ?? null,
            'password'      => Hash::make($validated['password']),
            'skor_reputasi' => 100,
            'status_aktif'  => 1,
        ]);

        $token = $pengendara->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'guard'   => 'pengendara',
            'user'    => [
                'id_pengendara' => $pengendara->id_pengendara,
                'nama_lengkap'  => $pengendara->nama_lengkap,
                'email'         => $pengendara->email,
                'username'      => $pengendara->username,
                'no_telepon'    => $pengendara->no_telepon,
                'skor_reputasi' => $pengendara->skor_reputasi,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/auth/logout
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
