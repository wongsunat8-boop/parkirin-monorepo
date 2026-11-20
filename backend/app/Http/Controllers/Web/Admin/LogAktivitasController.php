<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;

        $logs = LogAktivitas::with(['user:id_user,nama_lengkap,role'])
            ->where('id_tempat_parkir', $tenantId)
            ->whereHas('user', fn($q) => $q->whereIn('role', ['owner', 'admin', 'petugas']))
            ->orderByDesc('id_log')
            ->cursorPaginate(20, ['*'], 'log_cursor')
            ->withQueryString();

        $transaksis = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara',
                'petugas:id_user,nama_lengkap',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->orderByDesc('id_transaksi')
            ->cursorPaginate(20, ['*'], 'trx_cursor')
            ->withQueryString();

        return view('admin.log_aktivitas.index', compact('logs', 'transaksis'));
    }
}
