<?php

use App\Http\Middleware\EnsurePengendaraActive;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TenantIsolationMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'              => RoleMiddleware::class,
            'tenant'            => TenantIsolationMiddleware::class,
            'pengendara.active' => EnsurePengendaraActive::class,
            'guest'             => RedirectIfAuthenticated::class,
            'redirect.if.auth'  => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            // 1. Jika sesi sudah berhasil masuk di salah satu guard (misal akibat double submit), langsung arahkan ke dashboard
            if (auth('web')->check()) {
                $user = auth('web')->user();
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

            if (auth('pengendara')->check()) {
                return redirect()->route('pengendara.dashboard');
            }

            // 2. Jika sesi kedaluwarsa pada proses login atau logout, arahkan kembali ke form login dengan notifikasi ramah
            if ($request->is('login*') || $request->is('logout')) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sesi login telah diperbarui. Silakan masukkan kembali kredensial Anda.']);
            }

            // 3. Fallback umum (halaman lain)
            return redirect()->back()
                ->withErrors(['session_expired' => 'Sesi formulir telah kedaluwarsa. Silakan ulangi pengiriman formulir.']);
        });
    })->create();
