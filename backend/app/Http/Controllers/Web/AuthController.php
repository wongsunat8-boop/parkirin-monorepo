<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengendara;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle staff (user) login.
     */
    public function loginUser(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('pengendara')->check()) {
            Auth::guard('pengendara')->logout();
        }

        if (!Auth::guard('web')->attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
        }

        /** @var User|null $user */
        $user = Auth::guard('web')->user()?->loadMissing('tempatParkir:id_tempat_parkir,status_aktif');

        if (!$user instanceof User) {
            Auth::guard('web')->logout();
            return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
        }

        // Pengecekan 1: status tenant (untuk user terikat tenant). Berlaku terlepas dari status akun.
        if ($user->id_tempat_parkir && !$user->isSuperAdmin()) {
            $tenantAktif = (int) ($user->tempatParkir->status_aktif ?? 0) === 1;
            if (!$tenantAktif) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['username' => 'Tenant telah dinonaktifkan. Anda tidak dapat masuk.'])->withInput();
            }
        }

        // Pengecekan 2: status akun user.
        if (!(int) $user->status_aktif) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['username' => 'User telah dinonaktifkan. Akun Anda tidak dapat digunakan untuk masuk.'])->withInput();
        }

        $request->session()->regenerate();

        return match ($user->role) {
            'super_admin' => redirect()->intended('/super-admin'),
            'owner'       => redirect()->intended('/owner'),
            'admin'       => redirect()->intended('/admin'),
            'petugas'     => redirect()->intended('/petugas'),
            default       => redirect('/'),
        };
    }

    /**
     * Handle pengendara login (accepts email or username).
     */
    public function loginPengendara(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::guard('pengendara')->attempt([$loginField => $credentials['email'], 'password' => $credentials['password']])) {
            return back()->withErrors(['email' => 'Email/Username atau password salah.'])->withInput();
        }

        /** @var Pengendara|null $pengendara */
        $pengendara = Auth::guard('pengendara')->user();

        if (!$pengendara instanceof Pengendara || !$pengendara->status_aktif) {
            Auth::guard('pengendara')->logout();
            return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.'])->withInput();
        }

        $request->session()->regenerate();

        if (empty($pengendara->username)) {
            return redirect()->route('auth.google.onboarding');
        }

        return redirect()->intended('/pengendara');
    }

    /**
     * Show the registration page.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle pengendara registration.
     */
    public function register(Request $request)
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

        Auth::guard('pengendara')->login($pengendara);
        $request->session()->regenerate();

        return redirect('/pengendara');
    }

    /**
     * Logout (any guard).
     */
    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('pengendara')->check()) {
            Auth::guard('pengendara')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
