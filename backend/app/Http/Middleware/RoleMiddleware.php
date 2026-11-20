<?php

namespace App\Http\Middleware;

use App\Models\Pengendara;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request for 5 roles:
     * super_admin, owner, admin, petugas, pengendara
     *
     * Usage:
     * ->middleware('role:owner,admin')
     * ->middleware('role:pengendara')
     * ->middleware('role:super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('web')
            ?? $request->user('pengendara')
            ?? $request->user('sanctum-user')
            ?? $request->user('sanctum-pengendara')
            ?? $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }
            return redirect()->route('login');
        }

        // Determine user's role across models
        $userRole = null;
        if ($user instanceof User) {
            $userRole = $user->role;
        } elseif ($user instanceof Pengendara) {
            $userRole = 'pengendara';
        }

        if (!$userRole) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Parse allowed roles (supports comma-separated strings)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki hak akses (Role: ' . $userRole . ') untuk membuka halaman ini.');
        }

        // Check account active status
        if (!(int) ($user->status_aktif ?? 0)) {
            $guard = ($user instanceof Pengendara) ? 'pengendara' : 'web';
            if (Auth::guard($guard)->check() && !$request->expectsJson()) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi pengelola.']);
            }
            abort(403, 'Akun telah dinonaktifkan.');
        }

        return $next($request);
    }
}
