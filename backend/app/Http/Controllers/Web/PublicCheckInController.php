<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicCheckInController extends Controller
{
    /**
     * Show QR Scanner view for Guests before entering vehicle data.
     */
    public function scanner()
    {
        $activeTenants = TempatParkir::where('status_aktif', 1)
            ->with(['tarif' => fn($q) => $q->where('status_aktif', 1)])
            ->withCount(['transaksi as terisi_count' => fn($q) => $q->where('status', 'masuk')])
            ->get();

        return view('checkin.scanner', compact('activeTenants'));
    }

    /**
     * Show Self Check-In Gate for walk-in guest after scanning tenant QR.
     */
    public function index($id_tempat_parkir)
    {
        $tempatParkir = TempatParkir::where(function ($q) use ($id_tempat_parkir) {
                $q->where('id_tempat_parkir', $id_tempat_parkir)
                  ->orWhere('kode_qr_gate', $id_tempat_parkir);
            })
            ->where('status_aktif', 1)
            ->firstOrFail();

        $terisi = Transaksi::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('status', 'masuk')
            ->count();

        $sisaSlot = max(0, $tempatParkir->kapasitas_reguler - $terisi);

        $tarifs = Tarif::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('status_aktif', 1)
            ->get();

        $supportedVehicles = $tarifs->pluck('jenis_kendaraan')->unique()->values()->all();
        $supportedTarifs = $tarifs->keyBy('jenis_kendaraan');

        return view('checkin.index', compact('tempatParkir', 'terisi', 'sisaSlot', 'tarifs', 'supportedVehicles', 'supportedTarifs'));
    }

    /**
     * Process Self Check-In with Fast-Track (Member/Booking) & Auto-Fallback support.
     */
    public function store(Request $request, $id_tempat_parkir)
    {
        $tempatParkir = TempatParkir::where(function ($q) use ($id_tempat_parkir) {
                $q->where('id_tempat_parkir', $id_tempat_parkir)
                  ->orWhere('kode_qr_gate', $id_tempat_parkir);
            })
            ->where('status_aktif', 1)
            ->firstOrFail();

        $tarifs = Tarif::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('status_aktif', 1)
            ->get();

        $supportedVehicles = $tarifs->pluck('jenis_kendaraan')->unique()->values()->all();

        if (empty($supportedVehicles)) {
            return back()->withErrors(['plat_nomor' => 'Tempat parkir ini belum membuka layanan tarif parkir reguler.'])->withInput();
        }

        $request->validate([
            'plat_nomor'      => 'required|string|max:15',
            'jenis_kendaraan' => ['required', Rule::in($supportedVehicles)],
            'warna'           => 'nullable|string|max:20',
        ]);

        $formattedPlat = Kendaraan::formatPlatNomor($request->input('plat_nomor'));
        $rawCleanPlat  = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->input('plat_nomor')));

        return DB::transaction(function () use ($tempatParkir, $formattedPlat, $rawCleanPlat, $request) {
            // Lock and check current capacity
            $terisi = Transaksi::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
                ->where('status', 'masuk')
                ->lockForUpdate()
                ->count();

            if ($terisi >= $tempatParkir->kapasitas_reguler) {
                return back()->withErrors(['plat_nomor' => 'Maaf, kapasitas parkir di lokasi ini sedang penuh.'])->withInput();
            }

            // Cari atau buat kendaraan
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
                    return back()->withErrors([
                        'plat_nomor' => 'Kendaraan dengan plat ' . $kendaraan->plat_nomor . ' saat ini masih berstatus parkir aktif di ' . $lokasi . ' (Karcis: ' . $transaksiAktif->kode_karcis . '). Tidak dapat check-in ganda sebelum keluar.'
                    ])->withInput();
                }
            }

            $authPengendaraId = auth('pengendara')->id();

            if (!$kendaraan) {
                $kendaraan = Kendaraan::create([
                    'plat_nomor'      => $formattedPlat,
                    'jenis_kendaraan' => $request->input('jenis_kendaraan'),
                    'warna'           => $request->input('warna') ?? 'Tidak Tercatat',
                    'id_pengendara'   => $authPengendaraId,
                ]);
            } else {
                $kendaraan->update([
                    'plat_nomor'      => $formattedPlat,
                    'jenis_kendaraan' => $request->input('jenis_kendaraan'),
                ]);
                if ($authPengendaraId && empty($kendaraan->id_pengendara)) {
                    $kendaraan->update(['id_pengendara' => $authPengendaraId]);
                }
            }

            if ($request->filled('warna') && ($kendaraan->warna === 'Tidak Tercatat' || empty($kendaraan->warna))) {
                $kendaraan->update(['warna' => $request->input('warna')]);
            }

            // Ambil skema tarif aktif untuk jenis kendaraan ini
            $tarif = Tarif::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
                ->where('jenis_kendaraan', $request->input('jenis_kendaraan'))
                ->where('status_aktif', 1)
                ->first();

            if (!$tarif) {
                return back()->withErrors(['plat_nomor' => 'Skema tarif untuk kendaraan ini belum dikonfigurasi.'])->withInput();
            }

            $kodeKarcis = 'PKR-' . strtoupper(Str::random(4)) . '-' . mt_rand(1000, 9999);
            $biayaDasarReguler = ($tarif->tarif_jam_pertama > 0) 
                ? $tarif->tarif_jam_pertama 
                : ($tarif->tarif_per_jam > 0 ? $tarif->tarif_per_jam : 0);

            // ========================================================
            // 1. FAST-TRACK JALUR 1: BOOKING INAP TERJADWAL
            // ========================================================
            $activeBooking = Booking::where('id_kendaraan', $kendaraan->id_kendaraan)
                ->where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
                ->where('status_pembayaran', 'Lunas')
                ->whereIn('status', ['Terjadwal', 'Aktif_Inap'])
                ->where('waktu_mulai_inap', '<=', now()->addHours(3)) // Toleransi check-in hingga 3 jam sebelum jadwal
                ->where('waktu_rencana_keluar', '>=', now())
                ->first();

            if ($activeBooking) {
                $activeBooking->update(['status' => 'Aktif_Inap']);

                $transaksi = Transaksi::create([
                    'kode_karcis'         => $kodeKarcis,
                    'id_tempat_parkir'    => $tempatParkir->id_tempat_parkir,
                    'id_kendaraan'        => $kendaraan->id_kendaraan,
                    'id_tarif'            => $tarif->id_tarif,
                    'id_booking'          => $activeBooking->id_booking,
                    'id_langganan'        => null,
                    'id_petugas_masuk'    => null, // Self Check-In
                    'id_petugas_keluar'   => null,
                    'waktu_masuk'         => now(),
                    'waktu_keluar'        => null,
                    'biaya_dasar'         => 0, // Sudah lunas di muka via booking
                    'denda_overstay'      => 0,
                    'denda_karcis_hilang' => 0,
                    'metode_pembayaran'   => 'Prepaid_Booking',
                    'status_pembayaran'   => 'Sudah Bayar',
                    'status'              => 'masuk',
                ]);

                return redirect()->route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis])
                    ->with('success', "Fast-Track Booking Inap Terverifikasi [{$activeBooking->kode_booking}]! Gerbang dibuka otomatis.");
            }

            // ========================================================
            // 2. FAST-TRACK JALUR 2: MEMBER PASS AKTIF & AUTO-FALLBACK
            // ========================================================
            $activeMember = LanggananMember::with('paket')
                ->where('id_kendaraan', $kendaraan->id_kendaraan)
                ->where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
                ->where('status_pembayaran', 'Lunas')
                ->where('status_langganan', 'Aktif')
                ->where('tanggal_mulai', '<=', now()->toDateString())
                ->get()
                ->first(function ($m) {
                    if (!$m->paket) return false;
                    return \Carbon\Carbon::parse($m->tanggal_mulai)->addDays($m->paket->durasi_hari)->gte(today());
                });

            if ($activeMember) {
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

                if ($isFallback) {
                    // AUTO-FALLBACK: Limit harian terlampaui, alihkan ke Karcis Reguler (Bayar Kasir saat keluar)
                    $transaksi = Transaksi::create([
                        'kode_karcis'         => $kodeKarcis,
                        'id_tempat_parkir'    => $tempatParkir->id_tempat_parkir,
                        'id_kendaraan'        => $kendaraan->id_kendaraan,
                        'id_tarif'            => $tarif->id_tarif,
                        'id_booking'          => null,
                        'id_langganan'        => null, // Dialihkan dari member
                        'id_petugas_masuk'    => null,
                        'id_petugas_keluar'   => null,
                        'waktu_masuk'         => now(),
                        'waktu_keluar'        => null,
                        'biaya_dasar'         => $biayaDasarReguler,
                        'denda_overstay'      => 0,
                        'denda_karcis_hilang' => 0,
                        'metode_pembayaran'   => 'Tunai',
                        'status_pembayaran'   => 'Belum Bayar',
                        'status'              => 'masuk',
                    ]);

                    return redirect()->route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis])
                        ->with('warning', "Limit Masuk Harian Member ({$limitHarian}x/hari) tercapai. Check-in dialihkan otomatis ke Karcis Reguler (Auto-Fallback).");
                }

                // Member Pass Fast Track Normal (Green Pass)
                $transaksi = Transaksi::create([
                    'kode_karcis'         => $kodeKarcis,
                    'id_tempat_parkir'    => $tempatParkir->id_tempat_parkir,
                    'id_kendaraan'        => $kendaraan->id_kendaraan,
                    'id_tarif'            => $tarif->id_tarif,
                    'id_booking'          => null,
                    'id_langganan'        => $activeMember->id_langganan,
                    'id_petugas_masuk'    => null,
                    'id_petugas_keluar'   => null,
                    'waktu_masuk'         => now(),
                    'waktu_keluar'        => null,
                    'biaya_dasar'         => 0, // Bebas biaya gerbang
                    'denda_overstay'      => 0,
                    'denda_karcis_hilang' => 0,
                    'metode_pembayaran'   => 'Member_Pass',
                    'status_pembayaran'   => 'Sudah Bayar',
                    'status'              => 'masuk',
                ]);

                return redirect()->route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis])
                    ->with('success', "Member Pass [{$activeMember->kode_member}] Terverifikasi! Akses Fast-Track Aktif. Gerbang terbuka.");
            }

            // ========================================================
            // 3. JALUR UMUM: WALK-IN REGULAR CHECK-IN
            // ========================================================
            $transaksi = Transaksi::create([
                'kode_karcis'         => $kodeKarcis,
                'id_tempat_parkir'    => $tempatParkir->id_tempat_parkir,
                'id_kendaraan'        => $kendaraan->id_kendaraan,
                'id_tarif'            => $tarif->id_tarif,
                'id_booking'          => null,
                'id_langganan'        => null,
                'id_petugas_masuk'    => null, // NULL = Self Check-In Walk-In
                'id_petugas_keluar'   => null,
                'waktu_masuk'         => now(),
                'waktu_keluar'        => null,
                'biaya_dasar'         => $biayaDasarReguler,
                'denda_overstay'      => 0,
                'denda_karcis_hilang' => 0,
                'metode_pembayaran'   => 'Tunai',
                'status_pembayaran'   => 'Belum Bayar',
                'status'              => 'masuk',
            ]);

            return redirect()->route('checkin.ticket', ['kode_karcis' => $transaksi->kode_karcis])
                ->with('success', 'Self Check-In Berhasil! Karcis Digital QR Anda telah terbit.');
        });
    }

    /**
     * Show digital ticket on browser for Walk-In Guest or Green Digital Pass for Members.
     */
    public function ticket($kode_karcis)
    {
        $transaksi = Transaksi::with(['tempatParkir', 'kendaraan', 'tarif', 'booking', 'langganan.paket'])
            ->where('kode_karcis', $kode_karcis)
            ->firstOrFail();

        return view('checkin.ticket', compact('transaksi'));
    }

    /**
     * Polling endpoint to check ticket checkout status in real-time.
     */
    public function ticketStatus($kode_karcis)
    {
        $transaksi = Transaksi::with(['tempatParkir', 'kendaraan'])
            ->where('kode_karcis', $kode_karcis)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Karcis tidak ditemukan.'
            ], 404);
        }

        $totalBiaya = (float)($transaksi->biaya_dasar ?? 0) + (float)($transaksi->denda_overstay ?? 0) + (float)($transaksi->denda_karcis_hilang ?? 0);

        return response()->json([
            'success'               => true,
            'kode_karcis'           => $transaksi->kode_karcis,
            'plat_nomor'            => $transaksi->kendaraan->plat_nomor ?? '-',
            'nama_tempat'           => $transaksi->tempatParkir->nama_tempat ?? 'Parkirin',
            'status'                => $transaksi->status,
            'is_keluar'             => $transaksi->status === 'keluar',
            'waktu_masuk'           => $transaksi->waktu_masuk ? \Carbon\Carbon::parse($transaksi->waktu_masuk)->translatedFormat('d M Y, H:i') : null,
            'waktu_keluar'          => $transaksi->waktu_keluar ? \Carbon\Carbon::parse($transaksi->waktu_keluar)->translatedFormat('d M Y, H:i') : null,
            'metode_pembayaran'     => $transaksi->metode_pembayaran,
            'status_pembayaran'     => $transaksi->status_pembayaran,
            'total_biaya'           => $totalBiaya,
            'total_biaya_formatted' => 'Rp ' . number_format($totalBiaya, 0, ',', '.'),
        ]);
    }
}
