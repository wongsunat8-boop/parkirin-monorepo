<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\TempatParkir;
use Illuminate\Http\Request;

class QrGateController extends Controller
{
    /**
     * Show QR Gerbang Masuk overview for the tenant.
     */
    public function index(Request $request)
    {
        $user = auth('web')->user();
        $tenant = TempatParkir::findOrFail($user->id_tempat_parkir);

        $checkInUrl = route('checkin.public', [
            'id_tempat_parkir' => $tenant->kode_qr_gate ?? $tenant->id_tempat_parkir
        ]);

        return view('admin.qr_gate.index', compact('tenant', 'checkInUrl'));
    }

    /**
     * Generate gate QR Code. Only permitted ONCE per tenant.
     */
    public function generate(Request $request)
    {
        $user = auth('web')->user();
        $tenant = TempatParkir::findOrFail($user->id_tempat_parkir);

        if ($tenant->hasGateQr()) {
            return back()->withErrors([
                'qr' => 'QR Gerbang Masuk sudah pernah dibuat sebelumnya. Sesuai kebijakan keamanan, QR ini hanya dapat digenerate 1 kali per tenant.'
            ]);
        }

        $code = $tenant->generateGateQr();

        // Audit log
        LogAktivitas::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_user'          => $user->id_user,
            'aktivitas'        => 'Generate QR Gerbang Masuk Tenant: ' . $code,
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('success', 'QR Gerbang Masuk berhasil dibuat dan diaktifkan permanen.');
    }

    /**
     * Printable poster standee view.
     */
    public function print(Request $request)
    {
        $user = auth('web')->user();
        $tenant = TempatParkir::findOrFail($user->id_tempat_parkir);

        if (!$tenant->hasGateQr()) {
            return redirect()->route('admin.qr-gate.index')
                ->withErrors(['qr' => 'Silakan generate QR Gerbang Masuk terlebih dahulu sebelum mencetak poster standee.']);
        }

        $checkInUrl = route('checkin.public', [
            'id_tempat_parkir' => $tenant->kode_qr_gate
        ]);

        return view('admin.qr_gate.print', compact('tenant', 'checkInUrl'));
    }
}
