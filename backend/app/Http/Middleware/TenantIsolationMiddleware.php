<?php

namespace App\Http\Middleware;

use App\Models\Pengendara;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantIsolationMiddleware
{
    /**
     * Ensure that tenant-bound users can only access data within their own tenant.
     * Aborts with 403 if the requested resource belongs to a different tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web') ?? $request->user('pengendara') ?? $request->user('sanctum-user') ?? $request->user();

        if (!$user) {
            abort(401);
        }

        // Pengendara does not belong to a specific tenant (customer across multiple locations)
        if ($user instanceof Pengendara) {
            return $next($request);
        }

        if (!$user instanceof User) {
            abort(401);
        }

        // Super Admin has no tenant restriction
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Tenant-bound users must have an id_tempat_parkir
        if (!$user->id_tempat_parkir) {
            abort(403, 'Akun tidak terkait dengan tempat parkir manapun.');
        }

        // Blokir sesi aktif jika tenant dinonaktifkan (mencegah perubahan data pada tenant nonaktif).
        $user->loadMissing('tempatParkir');
        $tenantAktif = (int) ($user->tempatParkir->status_aktif ?? 0) === 1;
        if (!$tenantAktif) {
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                if ($request->expectsJson()) {
                    abort(403, 'Tenant telah dinonaktifkan. Anda tidak dapat mengakses sistem.');
                }
                return redirect()->route('login')->withErrors(['username' => 'Tenant telah dinonaktifkan. Anda tidak dapat mengakses sistem.']);
            }
            abort(403, 'Tenant telah dinonaktifkan. Anda tidak dapat mengakses sistem.');
        }

        // Check if the route or request input has an id_tempat_parkir parameter
        $routeTenantId = $request->route('id_tempat_parkir')
            ?? $request->route('tenant')
            ?? $request->input('id_tempat_parkir');

        if ($routeTenantId !== null && (int) $routeTenantId !== (int) $user->id_tempat_parkir) {
            abort(403, 'Anda tidak memiliki akses ke data tenant ini.');
        }

        // Share tenant_id for downstream use in controllers & queries
        $request->attributes->set('tenant_id', $user->id_tempat_parkir);

        return $next($request);
    }
}
