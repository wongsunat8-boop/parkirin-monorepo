<?php

namespace App\Http\Controllers\Web\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ScannerKeluarController extends Controller
{
    /**
     * Tampilkan halaman Scanner Keluar (html5-qrcode).
     */
    public function index()
    {
        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;
        $tenant = TempatParkir::findOrFail($tenantId);

        return view('petugas.scanner_keluar', compact('tenant'));
    }

    /**
     * Cari transaksi berdasarkan kode karcis yang di-scan.
     * Hitung biaya via MySQL Stored Function fn_hitung_biaya_parkir.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'kode_karcis' => 'required|string|max:50',
        ]);

        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;

        $transaksi = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan,warna',
                'tarif:id_tarif,jenis_kendaraan,tipe_tarif,tarif_per_jam,tarif_jam_pertama,tarif_berikutnya,tarif_inap_harian,denda_karcis_hilang',
                'langganan.paket:id_paket,nama_paket',
                'booking:id_booking,kode_booking',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->where('kode_karcis', $request->kode_karcis)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Karcis tidak ditemukan di lokasi parkir ini. Periksa kembali kode QR yang dipindai.',
            ], 404);
        }

        if ($transaksi->status === 'keluar') {
            return response()->json([
                'success' => false,
                'message' => 'Kendaraan ini sudah keluar sebelumnya pada ' . optional($transaksi->waktu_keluar)->translatedFormat('d M Y, H:i') . '.',
            ], 422);
        }

        // Hitung durasi dan jumlah pergantian tengah malam (00:00)
        $waktuMasuk = \Carbon\Carbon::parse($transaksi->waktu_masuk);
        $waktuSekarang = now();
        $durasiJam = max(1, (int) ceil($waktuMasuk->floatDiffInHours($waktuSekarang)));
        $durasiFormatted = $waktuMasuk->diffForHumans($waktuSekarang, true);
        $jumlahMalam = max(0, (int) $waktuMasuk->copy()->startOfDay()->diffInDays($waktuSekarang->copy()->startOfDay()));

        // Cek apakah Member Pass atau Booking Prepaid (biaya = Rp0)
        $isMember = $transaksi->metode_pembayaran === 'Member_Pass' && $transaksi->status_pembayaran === 'Sudah Bayar';
        $isBooking = $transaksi->metode_pembayaran === 'Prepaid_Booking' && $transaksi->status_pembayaran === 'Sudah Bayar';

        $biayaDasar = 0;
        $dendaKarcisHilang = (float) $transaksi->denda_karcis_hilang;
        $dendaOverstay = (float) $transaksi->denda_overstay;

        if ($isMember || $isBooking) {
            // Gratis — sudah lunas
            $biayaDasar = 0;

            // Cek overstay booking
            if ($isBooking && $transaksi->booking) {
                $rencanaKeluar = \Carbon\Carbon::parse($transaksi->booking->waktu_rencana_keluar);
                if ($waktuSekarang->gt($rencanaKeluar)) {
                    $jamOverstay = max(1, (int) ceil($rencanaKeluar->floatDiffInHours($waktuSekarang)));
                    $malamOverstay = max(0, (int) $rencanaKeluar->copy()->startOfDay()->diffInDays($waktuSekarang->copy()->startOfDay()));
                    // Hitung overstay via stored function
                    $dendaOverstay = (float) DB::selectOne(
                        'SELECT fn_hitung_biaya_parkir(?, ?, ?) AS biaya',
                        [$transaksi->id_tarif, $jamOverstay, $malamOverstay]
                    )->biaya;
                }
            }
        } else {
            // Reguler: hitung biaya via stored function
            $biayaDasar = (float) DB::selectOne(
                'SELECT fn_hitung_biaya_parkir(?, ?, ?) AS biaya',
                [$transaksi->id_tarif, $durasiJam, $jumlahMalam]
            )->biaya;
        }

        $totalBiaya = $biayaDasar + $dendaOverstay + $dendaKarcisHilang;

        return response()->json([
            'success'     => true,
            'transaksi'   => [
                'id_transaksi'      => $transaksi->id_transaksi,
                'kode_karcis'       => $transaksi->kode_karcis,
                'plat_nomor'        => $transaksi->kendaraan->plat_nomor ?? '-',
                'jenis_kendaraan'   => $transaksi->kendaraan->jenis_kendaraan ?? '-',
                'warna'             => $transaksi->kendaraan->warna ?? '-',
                'waktu_masuk'       => $waktuMasuk->translatedFormat('d M Y, H:i'),
                'durasi'            => $durasiFormatted,
                'durasi_jam'        => $durasiJam,
                'jumlah_malam'      => $jumlahMalam,
                'is_inap'           => $jumlahMalam > 0,
                'tipe_tarif'        => $transaksi->tarif->tipe_tarif ?? '-',
                'is_member'         => $isMember,
                'is_booking'        => $isBooking,
                'nama_paket'        => $isMember ? ($transaksi->langganan->paket->nama_paket ?? 'Member') : null,
                'kode_booking'      => $isBooking ? ($transaksi->booking->kode_booking ?? '-') : null,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'biaya_dasar'       => $biayaDasar,
                'denda_overstay'    => $dendaOverstay,
                'denda_karcis_hilang' => $dendaKarcisHilang,
                'total_biaya'       => $totalBiaya,
            ],
        ]);
    }

    /**
     * Proses pembayaran dan selesaikan transaksi keluar.
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'id_transaksi'        => 'required|integer|exists:transaksi,id_transaksi',
            'metode_bayar'        => 'required|string|in:Tunai,QRIS_Mitra,Member_Pass,Prepaid_Booking',
            'denda_karcis_hilang' => 'nullable|numeric|min:0',
            'identitas_pengambil' => 'nullable|string|max:100',
        ]);

        $user = Auth::guard('web')->user();
        $tenantId = $user->id_tempat_parkir;

        return DB::transaction(function () use ($request, $user, $tenantId) {
            $transaksi = Transaksi::where('id_transaksi', $request->id_transaksi)
                ->where('id_tempat_parkir', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data karcis transaksi tidak ditemukan di lokasi parkir ini.',
                ], 404);
            }

            if ($transaksi->status === 'keluar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi karcis ' . $transaksi->kode_karcis . ' sudah selesai/keluar sebelumnya.',
                ], 400);
            }

            $waktuKeluar = now();
            $waktuMasuk = \Carbon\Carbon::parse($transaksi->waktu_masuk);
            $durasiJam = max(1, (int) ceil($waktuMasuk->floatDiffInHours($waktuKeluar)));
            $jumlahMalam = max(0, (int) $waktuMasuk->copy()->startOfDay()->diffInDays($waktuKeluar->copy()->startOfDay()));

            $isMember = $transaksi->metode_pembayaran === 'Member_Pass' && $transaksi->status_pembayaran === 'Sudah Bayar';
            $isBooking = $transaksi->metode_pembayaran === 'Prepaid_Booking' && $transaksi->status_pembayaran === 'Sudah Bayar';

            $biayaDasar = 0;
            $dendaOverstay = (float) $transaksi->denda_overstay;
            $dendaKarcisHilang = $request->denda_karcis_hilang ?? (float) $transaksi->denda_karcis_hilang;
            $identitasPengambil = $request->identitas_pengambil;

            if ($isMember || $isBooking) {
                $biayaDasar = 0;

                // Hitung overstay booking
                if ($isBooking && $transaksi->id_booking) {
                    $booking = $transaksi->booking;
                    if ($booking && $waktuKeluar->gt(\Carbon\Carbon::parse($booking->waktu_rencana_keluar))) {
                        $rencanaKeluar = \Carbon\Carbon::parse($booking->waktu_rencana_keluar);
                        $jamOverstay = max(1, (int) ceil($rencanaKeluar->floatDiffInHours($waktuKeluar)));
                        $malamOverstay = max(0, (int) $rencanaKeluar->copy()->startOfDay()->diffInDays($waktuKeluar->copy()->startOfDay()));
                        $dendaOverstay = (float) DB::selectOne(
                            'SELECT fn_hitung_biaya_parkir(?, ?, ?) AS biaya',
                            [$transaksi->id_tarif, $jamOverstay, $malamOverstay]
                        )->biaya;
                    }
                }
            } else {
                $biayaDasar = (float) DB::selectOne(
                    'SELECT fn_hitung_biaya_parkir(?, ?, ?) AS biaya',
                    [$transaksi->id_tarif, $durasiJam, $jumlahMalam]
                )->biaya;
            }

            $totalBiaya = $biayaDasar + $dendaOverstay + $dendaKarcisHilang;
            $sudahLunas = ($isMember || $isBooking) && $totalBiaya <= 0;

            $metodeBayarFinal = $sudahLunas
                ? $transaksi->metode_pembayaran
                : (in_array($request->metode_bayar, ['Member_Pass', 'Prepaid_Booking']) ? 'Tunai' : $request->metode_bayar);

            $transaksi->update([
                'waktu_keluar'        => $waktuKeluar,
                'biaya_dasar'         => $biayaDasar,
                'denda_overstay'      => $dendaOverstay,
                'denda_karcis_hilang' => $dendaKarcisHilang,
                'identitas_pengambil' => $identitasPengambil,
                'metode_pembayaran'   => $metodeBayarFinal,
                'status_pembayaran'   => 'Sudah Bayar',
                'status'              => 'keluar',
                'id_petugas_keluar'   => $user->id_user,
            ]);

            // Update status booking jika ada
            if ($transaksi->id_booking) {
                $transaksi->booking?->update(['status' => 'Selesai']);
            }

            // Log aktivitas
            LogAktivitas::create([
                'id_tempat_parkir' => $tenantId,
                'id_user'          => $user->id_user,
                'aktivitas'        => "Checkout kendaraan [{$transaksi->kendaraan->plat_nomor}] — Karcis: {$transaksi->kode_karcis}, Total: Rp " . number_format($totalBiaya, 0, ',', '.'),
                'waktu_aktivitas'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil keluar! Tagihan sebesar Rp ' . number_format($totalBiaya, 0, ',', '.') . ' telah diselesaikan.',
                'data'    => [
                    'kode_karcis'  => $transaksi->kode_karcis,
                    'plat_nomor'   => $transaksi->kendaraan->plat_nomor,
                    'total_biaya'  => $totalBiaya,
                    'waktu_keluar' => $waktuKeluar->translatedFormat('d M Y, H:i'),
                ],
            ]);
        });
    }

    /**
     * Cari transaksi aktif berdasarkan plat nomor (SOP Karcis Hilang).
     */
    public function searchByPlat(Request $request)
    {
        $request->validate([
            'plat_nomor' => 'required|string|max:15',
        ]);

        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;
        $plat = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->plat_nomor));
        $formattedPlat = Kendaraan::formatPlatNomor($request->plat_nomor);

        $transaksi = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan,warna',
                'tarif:id_tarif,jenis_kendaraan,tipe_tarif,denda_karcis_hilang',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status', 'masuk')
            ->whereHas('kendaraan', function ($q) use ($plat, $formattedPlat) {
                $q->where('plat_nomor', $formattedPlat)
                  ->orWhere('plat_nomor', $plat);
            })
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ditemukan kendaraan dengan plat nomor tersebut yang sedang parkir di lokasi ini.',
            ], 404);
        }

        $waktuMasuk = \Carbon\Carbon::parse($transaksi->waktu_masuk);
        $waktuSekarang = now();
        $durasiJam = max(1, (int) ceil($waktuMasuk->floatDiffInHours($waktuSekarang)));
        $jumlahMalam = max(0, (int) $waktuMasuk->copy()->startOfDay()->diffInDays($waktuSekarang->copy()->startOfDay()));

        $biayaDasar = (float) DB::selectOne(
            'SELECT fn_hitung_biaya_parkir(?, ?, ?) AS biaya',
            [$transaksi->id_tarif, $durasiJam, $jumlahMalam]
        )->biaya;

        $dendaKarcisHilang = (float) ($transaksi->tarif->denda_karcis_hilang ?? 0);

        return response()->json([
            'success'   => true,
            'transaksi' => [
                'id_transaksi'    => $transaksi->id_transaksi,
                'kode_karcis'     => $transaksi->kode_karcis,
                'plat_nomor'      => $transaksi->kendaraan->plat_nomor ?? '-',
                'jenis_kendaraan' => $transaksi->kendaraan->jenis_kendaraan ?? '-',
                'warna'           => $transaksi->kendaraan->warna ?? '-',
                'waktu_masuk'     => $waktuMasuk->translatedFormat('d M Y, H:i'),
                'durasi'          => $waktuMasuk->diffForHumans($waktuSekarang, true),
                'durasi_jam'      => $durasiJam,
                'jumlah_malam'    => $jumlahMalam,
                'is_inap'         => $jumlahMalam > 0,
                'tipe_tarif'      => $transaksi->tarif->tipe_tarif ?? '-',
                'is_member'       => false,
                'is_booking'      => false,
                'biaya_dasar'     => $biayaDasar,
                'denda_overstay'  => 0,
                'denda_karcis_hilang' => $dendaKarcisHilang,
                'total_biaya'     => $biayaDasar + $dendaKarcisHilang,
                'is_lost_ticket'  => true,
            ],
        ]);
    }
}
