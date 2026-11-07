<?php

namespace App\Http\Middleware;

use App\Models\Pengendara;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePengendaraActive
{
    /**
     * Ensure the authenticated pengendara account is active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pengendara = $request->user('pengendara') ?? $request->user('sanctum-pengendara') ?? $request->user();

        if ($pengendara instanceof Pengendara && !$pengendara->status_aktif) {
            abort(403, 'Akun Anda telah dinonaktifkan.');
        }

        return $next($request);
    }
}
