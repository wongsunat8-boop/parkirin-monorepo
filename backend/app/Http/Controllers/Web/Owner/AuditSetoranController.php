<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\LanggananMember;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditSetoranController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $dari = $request->input('dari', now()->subDays(6)->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());
        $petugasId = $request->input('id_petugas');

        // Query aggregasi per shift (Tanggal Keluar + Petugas Kasir)
        $shifts = Transaksi::query()
            ->where('transaksi.id_tempat_parkir', $tenantId)
            ->whereNotNull('waktu_keluar')
            ->where('status_pembayaran', 'Sudah Bayar')
            ->when($dari, fn($q) => $q->whereDate('waktu_keluar', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('waktu_keluar', '<=', $sampai))
            ->when($petugasId, fn($q) => $q->where('id_petugas_keluar', $petugasId))
            ->select([
                DB::raw('DATE(waktu_keluar) as tanggal_shift'),
                'id_petugas_keluar',
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('SUM(CASE WHEN metode_pembayaran = "Tunai" THEN (biaya_dasar + denda_overstay + denda_karcis_hilang) ELSE 0 END) as setoran_tunai'),
                DB::raw('SUM(CASE WHEN metode_pembayaran != "Tunai" THEN (biaya_dasar + denda_overstay + denda_karcis_hilang) ELSE 0 END) as non_tunai'),
                DB::raw('SUM(biaya_dasar + denda_overstay + denda_karcis_hilang) as total_omzet'),
            ])
            ->groupBy('tanggal_shift', 'id_petugas_keluar')
            ->orderByDesc('tanggal_shift')
            ->paginate(15)
            ->withQueryString();

        // Load relasi user petugas kasir
        $petugasIds = $shifts->pluck('id_petugas_keluar')->filter()->unique();
        $petugasMap = User::whereIn('id_user', $petugasIds)->get()->keyBy('id_user');

        // Transaksi Non-Tunai Online: Member Pass Lunas & Booking Inap Lunas
        $memberQuery = LanggananMember::with(['pengendara', 'kendaraan', 'paket', 'userVerifikasi'])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($dari, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $sampai));

        $bookingQuery = Booking::with(['pengendara', 'kendaraan', 'userVerifikasi'])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($dari, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $sampai));

        $nonTunaiMember = $petugasId ? 0.0 : (float) (clone $memberQuery)->sum('biaya_langganan');
        $nonTunaiBooking = $petugasId ? 0.0 : (float) (clone $bookingQuery)->sum('biaya_inap_dibayar');
        $memberList = $petugasId ? collect() : (clone $memberQuery)->orderByDesc('id_langganan')->get();
        $bookingList = $petugasId ? collect() : (clone $bookingQuery)->orderByDesc('id_booking')->get();

        $totalTunaiKasir = (float) Transaksi::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Sudah Bayar')
            ->where('metode_pembayaran', 'Tunai')
            ->when($dari, fn($q) => $q->whereDate('waktu_keluar', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('waktu_keluar', '<=', $sampai))
            ->when($petugasId, fn($q) => $q->where('id_petugas_keluar', $petugasId))
            ->sum(DB::raw('biaya_dasar + denda_overstay + denda_karcis_hilang'));

        $totalNonTunaiKasir = (float) Transaksi::where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Sudah Bayar')
            ->where('metode_pembayaran', '!=', 'Tunai')
            ->when($dari, fn($q) => $q->whereDate('waktu_keluar', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('waktu_keluar', '<=', $sampai))
            ->when($petugasId, fn($q) => $q->where('id_petugas_keluar', $petugasId))
            ->sum(DB::raw('biaya_dasar + denda_overstay + denda_karcis_hilang'));

        $totalNonTunai = $totalNonTunaiKasir + $nonTunaiMember + $nonTunaiBooking;

        // Total ringkasan periode
        $summary = [
            'total_tunai' => $totalTunaiKasir,
            'total_non_tunai' => $totalNonTunai,
            'non_tunai_kasir' => $totalNonTunaiKasir,
            'non_tunai_member' => $nonTunaiMember,
            'non_tunai_booking' => $nonTunaiBooking,
            'grand_total' => $totalTunaiKasir + $totalNonTunai,
        ];

        // Daftar Petugas untuk filter dropdown
        $daftarPetugas = User::where('id_tempat_parkir', $tenantId)
            ->where('role', 'petugas')
            ->orderBy('nama_lengkap')
            ->get(['id_user', 'nama_lengkap']);

        return view('owner.audit_setoran.index', compact(
            'shifts',
            'petugasMap',
            'summary',
            'daftarPetugas',
            'dari',
            'sampai',
            'petugasId',
            'memberList',
            'bookingList'
        ));
    }

    public function print(Request $request)
    {
        $tenantId = $this->tenantId();
        $tenant = TempatParkir::findOrFail($tenantId);
        $dari = $request->input('dari', now()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());
        $petugasId = $request->input('id_petugas');

        $shifts = Transaksi::query()
            ->where('transaksi.id_tempat_parkir', $tenantId)
            ->whereNotNull('waktu_keluar')
            ->where('status_pembayaran', 'Sudah Bayar')
            ->when($dari, fn($q) => $q->whereDate('waktu_keluar', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('waktu_keluar', '<=', $sampai))
            ->when($petugasId, fn($q) => $q->where('id_petugas_keluar', $petugasId))
            ->select([
                DB::raw('DATE(waktu_keluar) as tanggal_shift'),
                'id_petugas_keluar',
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('SUM(CASE WHEN metode_pembayaran = "Tunai" THEN (biaya_dasar + denda_overstay + denda_karcis_hilang) ELSE 0 END) as setoran_tunai'),
                DB::raw('SUM(CASE WHEN metode_pembayaran != "Tunai" THEN (biaya_dasar + denda_overstay + denda_karcis_hilang) ELSE 0 END) as non_tunai'),
                DB::raw('SUM(biaya_dasar + denda_overstay + denda_karcis_hilang) as total_omzet'),
            ])
            ->groupBy('tanggal_shift', 'id_petugas_keluar')
            ->orderBy('tanggal_shift')
            ->get();

        $petugasIds = $shifts->pluck('id_petugas_keluar')->filter()->unique();
        $petugasMap = User::whereIn('id_user', $petugasIds)->get()->keyBy('id_user');

        // Transaksi Non-Tunai Online: Member Pass Lunas & Booking Inap Lunas
        $memberQuery = LanggananMember::with(['pengendara', 'kendaraan', 'paket', 'userVerifikasi'])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($dari, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_registrasi, tanggal_mulai)'), '<=', $sampai));

        $bookingQuery = Booking::with(['pengendara', 'kendaraan', 'userVerifikasi'])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status_pembayaran', 'Lunas')
            ->when($dari, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate(DB::raw('COALESCE(waktu_unggah_bukti, waktu_booking)'), '<=', $sampai));

        $nonTunaiMember = $petugasId ? 0.0 : (float) (clone $memberQuery)->sum('biaya_langganan');
        $nonTunaiBooking = $petugasId ? 0.0 : (float) (clone $bookingQuery)->sum('biaya_inap_dibayar');
        $memberList = $petugasId ? collect() : (clone $memberQuery)->orderByDesc('id_langganan')->get();
        $bookingList = $petugasId ? collect() : (clone $bookingQuery)->orderByDesc('id_booking')->get();

        $totalTunai = $shifts->sum('setoran_tunai');
        $totalNonTunaiKasir = $shifts->sum('non_tunai');
        $totalNonTunai = $totalNonTunaiKasir + $nonTunaiMember + $nonTunaiBooking;
        $grandTotal = $totalTunai + $totalNonTunai;

        return view('owner.audit_setoran.print', compact(
            'tenant',
            'shifts',
            'petugasMap',
            'totalTunai',
            'totalNonTunai',
            'totalNonTunaiKasir',
            'nonTunaiMember',
            'nonTunaiBooking',
            'grandTotal',
            'dari',
            'sampai',
            'memberList',
            'bookingList',
            'petugasId'
        ));
    }
}
