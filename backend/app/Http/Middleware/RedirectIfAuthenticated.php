<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Jika sudah masuk (login), arahkan langsung ke halaman utama sesuai role.
     * Tidak boleh mengakses halaman landing (/), login, atau register jika masih ada sesi aktif.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Cek guard pengendara
        if (Auth::guard('pengendara')->check()) {
            $pengendara = Auth::guard('pengendara')->user();
            if ($pengendara && $pengendara->status_aktif) {
                return redirect()->route('pengendara.dashboard');
            }
        }

        // Cek guard web (staff: super_admin, owner, admin, petugas)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user && $user->status_aktif) {
                return match ($user->role) {
                    'super_admin' => redirect()->route('super-admin.dashboard'),
                    'owner'       => redirect()->route('owner.dashboard'),
                    'admin'       => redirect()->route('admin.dashboard'),
                    'petugas'     => redirect()->route('petugas.dashboard'),
                    default       => redirect('/'),
                };
            }
        }

        return $next($request);
    }
}
