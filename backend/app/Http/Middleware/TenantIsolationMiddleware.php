<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantIsolationMiddleware
{
    /**
     * Ensure that tenant-bound users can only access data within their own tenant.
     * Aborts with 403 if the requested resource belongs to a different tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web') ?? $request->user('sanctum-user') ?? $request->user();

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
