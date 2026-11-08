<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:owner,admin') or ->middleware('role:super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('web') ?? $request->user('sanctum-user') ?? $request->user();

        if (!$user instanceof User) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (!in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if (!(int) $user->status_aktif) {
            if (Auth::guard('web')->check() && !$request->expectsJson()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['username' => 'User telah dinonaktifkan. Akun Anda tidak dapat mengakses sistem.']);
            }
            abort(403, 'User telah dinonaktifkan. Akun Anda tidak dapat mengakses sistem.');
        }

        return $next($request);
    }
}
