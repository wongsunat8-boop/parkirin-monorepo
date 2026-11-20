<?php

namespace App\Http\Controllers\Web\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\LogAktivitas;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Models\LanggananMember;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::guard('web')->user()->id_tempat_parkir;
        $tenant = TempatParkir::findOrFail($tenantId);

        $tarifs = Tarif::where('id_tempat_parkir', $tenantId)
            ->where('status_aktif', 1)
            ->get();

        $supportedVehicles = $tarifs->pluck('jenis_kendaraan')->unique()->values()->all();

        $transaksiAktif = Transaksi::with([
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'pengendara',
                'tarif:id_tarif,jenis_kendaraan,tipe_tarif',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->orderByDesc('waktu_masuk')
            ->paginate(15);

        return view('petugas.dashboard', compact('tenant', 'transaksiAktif', 'supportedVehicles'));
    }

    /**
     * Input Manual (Fallback) — Petugas mencatat kendaraan masuk manual.
     * Digunakan saat HP pengendara mati/tidak bisa scan QR sendiri.
     */
    public function manualCheckIn(Request $request)
    {
        $user = Auth::guard('web')->user();
        $tenantId = $user->id_tempat_parkir;
        $tenant = TempatParkir::findOrFail($tenantId);

        $tarifs = Tarif::where('id_tempat_parkir', $tenantId)
            ->where('status_aktif', 1)
            ->get();

        $supportedVehicles = $tarifs->pluck('jenis_kendaraan')->unique()->values()->all();

        $request->validate([
            'plat_nomor'      => 'required|string|max:15',
            'jenis_kendaraan' => ['required', Rule::in($supportedVehicles)],
            'warna'           => 'nullable|string|max:20',
        ]);

        $formattedPlat = Kendaraan::formatPlatNomor($request->input('plat_nomor'));
        $rawCleanPlat = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($request->input('plat_nomor')));

        return DB::transaction(function () use ($tenant, $formattedPlat, $rawCleanPlat, $request, $user) {
            // Lock dan cek kapasitas
            $terisi = Transaksi::where('id_tempat_parkir', $tenant->id_tempat_parkir)
                ->where('status', 'masuk')
                ->lockForUpdate()
                ->count();

            if ($terisi >= $tenant->kapasitas_reguler) {
                return back()->with('error', 'Maaf, lahan parkir sedang penuh. Tidak bisa menambah kendaraan baru.')->withInput();
            }

            // Cari kendaraan
            $kendaraan = Kendaraan::where('plat_nomor', $formattedPlat)
                ->orWhere('plat_nomor', $rawCleanPlat)
                ->orWhereRaw("REPLACE(plat_nomor, ' ', '') = ?", [$rawCleanPlat])
                ->first();

            // Cegah check-in ganda jika kendaraan masih berstatus parkir aktif (status = masuk)
            if ($kendaraan) {
                $transaksiAktif = Transaksi::with('tempatParkir:id_tempat_parkir,nama_tempat')
                    ->where('id_kendaraan', $kendaraan->id_kendaraan)
                    ->where('status', 'masuk')
                    ->first();

                if ($transaksiAktif) {
                    $lokasi = $transaksiAktif->tempatParkir->nama_tempat ?? 'area parkir';
                    return back()->with('error', 'Kendaraan dengan plat ' . $kendaraan->plat_nomor . ' saat ini masih berstatus parkir aktif di ' . $lokasi . ' (Karcis: ' . $transaksiAktif->kode_karcis . '). Tidak dapat dicatat masuk lagi sebelum menyelesaikan transaksi sebelumnya.')->withInput();
                }
            }

            if (!$kendaraan) {
                $kendaraan = Kendaraan::create([
                    'plat_nomor'      => $formattedPlat,
                    'jenis_kendaraan' => $request->input('jenis_kendaraan'),
                    'warna'           => $request->input('warna') ?? 'Tidak Tercatat',
                    'id_pengendara'   => null,
                ]);
            } else {
                $kendaraan->update([
                    'jenis_kendaraan' => $request->input('jenis_kendaraan'),
                ]);
                if ($request->filled('warna') && ($kendaraan->warna === 'Tidak Tercatat' || empty($kendaraan->warna))) {
                    $kendaraan->update(['warna' => $request->input('warna')]);
                }
            }

            // Ambil tarif
            $tarif = Tarif::where('id_tempat_parkir', $tenant->id_tempat_parkir)
                ->where('jenis_kendaraan', $request->input('jenis_kendaraan'))
                ->where('status_aktif', 1)
                ->first();

            if (!$tarif) {
                return back()->with('error', 'Tarif untuk jenis kendaraan ini belum diatur oleh Admin.');
            }

            $kodeKarcis = 'PKR-' . strtoupper(Str::random(4)) . '-' . mt_rand(1000, 9999);
            $biayaDasar = ($tarif->tarif_jam_pertama > 0) ? $tarif->tarif_jam_pertama : ($tarif->tarif_per_jam > 0 ? $tarif->tarif_per_jam : 0);

            // Cek Fast-Track Member
            $activeMember = null;
            if ($kendaraan->id_pengendara) {
                $activeMember = LanggananMember::with('paket')
                    ->where('id_kendaraan', $kendaraan->id_kendaraan)
                    ->where('id_tempat_parkir', $tenant->id_tempat_parkir)
                    ->where('status_pembayaran', 'Lunas')
                    ->where('status_langganan', 'Aktif')
                    ->where('tanggal_mulai', '<=', now()->toDateString())
                    ->get()
                    ->first(function ($m) {
                        if (!$m->paket) return false;
                        return \Carbon\Carbon::parse($m->tanggal_mulai)->addDays($m->paket->durasi_hari)->gte(today());
                    });
            }

            if ($activeMember) {
                // Cek batas harian
                $limitHarian = $activeMember->paket?->batas_masuk_harian;
                $isFallback = false;

                if ($limitHarian && $limitHarian > 0) {
                    $masukHariIni = Transaksi::where('id_langganan', $activeMember->id_langganan)
                        ->whereDate('waktu_masuk', today())
                        ->count();
                    if ($masukHariIni >= $limitHarian) {
                        $isFallback = true;
                    }
                }

                if (!$isFallback) {
                    $transaksi = Transaksi::create([
                        'kode_karcis'         => $kodeKarcis,
                        'id_tempat_parkir'    => $tenant->id_tempat_parkir,
                        'id_kendaraan'        => $kendaraan->id_kendaraan,
                        'id_tarif'            => $tarif->id_tarif,
                        'id_langganan'        => $activeMember->id_langganan,
                        'id_petugas_masuk'    => $user->id_user,
                        'waktu_masuk'         => now(),
                        'biaya_dasar'         => 0,
                        'denda_overstay'      => 0,
                        'denda_karcis_hilang' => 0,
                        'metode_pembayaran'   => 'Member_Pass',
                        'status_pembayaran'   => 'Sudah Bayar',
                        'status'              => 'masuk',
                    ]);

                    LogAktivitas::create([
                        'id_tempat_parkir' => $tenant->id_tempat_parkir,
                        'id_user'          => $user->id_user,
                        'aktivitas'        => "Input manual masuk (Member Pass) [{$formattedPlat}] — Karcis: {$kodeKarcis}",
                        'waktu_aktivitas'  => now(),
                    ]);

                    return back()->with('success', "Kendaraan {$formattedPlat} berhasil dicatat masuk (Member Pass). Kode karcis: {$kodeKarcis}");
                }
            }

            // Reguler / Fallback
            $transaksi = Transaksi::create([
                'kode_karcis'         => $kodeKarcis,
                'id_tempat_parkir'    => $tenant->id_tempat_parkir,
                'id_kendaraan'        => $kendaraan->id_kendaraan,
                'id_tarif'            => $tarif->id_tarif,
                'id_petugas_masuk'    => $user->id_user,
                'waktu_masuk'         => now(),
                'biaya_dasar'         => $biayaDasar,
                'denda_overstay'      => 0,
                'denda_karcis_hilang' => 0,
                'metode_pembayaran'   => 'Tunai',
                'status_pembayaran'   => 'Belum Bayar',
                'status'              => 'masuk',
            ]);

            LogAktivitas::create([
                'id_tempat_parkir' => $tenant->id_tempat_parkir,
                'id_user'          => $user->id_user,
                'aktivitas'        => "Input manual masuk [{$formattedPlat}] — Karcis: {$kodeKarcis}",
                'waktu_aktivitas'  => now(),
            ]);

            return back()->with('success', "Kendaraan {$formattedPlat} berhasil dicatat masuk. Kode karcis: {$kodeKarcis}");
        });
    }
}
