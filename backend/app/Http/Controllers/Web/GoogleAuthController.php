<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengendara;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as GoogleUser;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth or display dev notice in local mode if credentials missing.
     */
    public function redirect(Request $request)
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        // Check if credentials are empty or default placeholder
        if (empty($clientId) || empty($clientSecret)) {
            if (app()->isLocal() || app()->environment('testing')) {
                // If the user submitted the simulation form or provided query params
                if ($request->isMethod('post') || $request->has('simulate')) {
                    $email = (string) $request->input('email', 'ghaniyyalbar@gmail.com');
                    $name = (string) $request->input('name', 'Ghaniyy Albar');
                    return $this->processGoogleUser($email, $name);
                }

                return view('auth.google-dev-notice');
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Google Client ID & Secret belum dikonfigurasi di file .env.',
            ]);
        }

        // Use stateless() to prevent InvalidStateException across localhost ports
        /** @var AbstractProvider $provider */
        $provider = Socialite::driver('google');
        return $provider->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback()
    {
        try {
            /** @var AbstractProvider $provider */
            $provider = Socialite::driver('google');
            /** @var GoogleUser $googleUser */
            $googleUser = $provider->stateless()->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal masuk dengan Google (' . $e->getMessage() . '). Kode otorisasi mungkin sudah kedaluwarsa, silakan klik ulang "Daftar dengan Google" dari awal.',
            ]);
        }

        return $this->processGoogleUser(
            $googleUser->getEmail(),
            $googleUser->getName() ?? 'Pengendara'
        );
    }

    /**
     * Common logic to authenticate or register Google user and redirect to onboarding if needed.
     */
    private function processGoogleUser(string $email, string $name)
    {
        $safeName = mb_substr($name, 0, 50);

        // Check if pengendara already exists with this email
        /** @var Pengendara|null $pengendara */
        $pengendara = Pengendara::where('email', $email)->first();

        /** @var StatefulGuard $guard */
        $guard = Auth::guard('pengendara');

        if ($pengendara) {
            if (!$pengendara->status_aktif) {
                return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
            }

            $guard->login($pengendara);
            session()->regenerate();

            if (empty($pengendara->username)) {
                return redirect()->route('auth.google.onboarding');
            }

            return redirect('/pengendara');
        }

        // New user — create account without username, redirect to onboarding
        /** @var Pengendara $pengendara */
        $pengendara = Pengendara::create([
            'nama_lengkap'  => $safeName,
            'email'         => $email,
            'username'      => null, // Will be set during onboarding
            'password'      => null, // Google OAuth, no password
            'skor_reputasi' => 100,
            'status_aktif'  => 1,
        ]);

        $guard->login($pengendara);
        session()->regenerate();

        return redirect()->route('auth.google.onboarding');
    }

    /**
     * Show the username onboarding form for Google OAuth users.
     */
    public function showOnboarding()
    {
        $user = Auth::guard('pengendara')->user();

        if (!$user instanceof Pengendara) {
            return redirect()->route('login');
        }

        // If username already set, redirect to dashboard
        if (!empty($user->username)) {
            return redirect('/pengendara');
        }

        return view('auth.google-onboarding', ['pengendara' => $user]);
    }

    /**
     * Store the username chosen during onboarding.
     */
    public function storeOnboarding(Request $request)
    {
        $user = Auth::guard('pengendara')->user();

        if (!$user instanceof Pengendara) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'username' => 'required|string|max:50|alpha_dash|unique:pengendara,username',
        ]);

        $user->update([
            'username' => $validated['username'],
        ]);

        return redirect('/pengendara');
    }
}
