<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\LanggananMember;
use App\Models\PaketLangganan;
use App\Models\Tarif;
use App\Models\TempatParkir;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(): View
    {
        $tempat = TempatParkir::with([
                'tarif' => fn ($q) => $q->where('status_aktif', 1)->orderBy('jenis_kendaraan'),
                'paketLangganans' => fn ($q) => $q->where('status_aktif', 1)
            ])
            ->where('status_aktif', 1)
            ->orderBy('nama_tempat')
            ->get();

        return view('pengendara.katalog.index', compact('tempat'));
    }

    public function show(TempatParkir $tempatParkir): View
    {
        abort_unless($tempatParkir->status_aktif, 404);

        $pengendara = auth('pengendara')->user();

        $tempatParkir->load([
            'tarif' => fn ($q) => $q->where('status_aktif', 1)->orderBy('jenis_kendaraan'),
            'paketLangganans' => fn ($q) => $q->where('status_aktif', 1)->orderBy('harga_paket')
        ]);

        // Kendaraan milik pengendara yang sedang login
        $kendaraanUser = Kendaraan::where('id_pengendara', $pengendara->id_pengendara)
            ->orderBy('plat_nomor')
            ->get();

        // Hitung ketersediaan slot inap aktif/terjadwal
        $terisiInap = Booking::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->whereIn('status', ['Terjadwal', 'Aktif_Inap'])
            ->count();
        $sisaInap = max(0, $tempatParkir->kuota_inap - $terisiInap);

        // Cek status member aktif per kendaraan pengendara khusus di tempat parkir INI
        $cleanPlats = $kendaraanUser->map(fn($k) => preg_replace('/[^A-Za-z0-9]/', '', strtoupper($k->plat_nomor)))->all();
        $activeMembersAtThisLocation = LanggananMember::with(['paket', 'kendaraan'])
            ->where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('status_langganan', 'Aktif')
            ->whereIn('status_pembayaran', ['Lunas', 'Menunggu Verifikasi'])
            ->where(function ($q) use ($kendaraanUser, $cleanPlats) {
                $q->whereIn('id_kendaraan', $kendaraanUser->pluck('id_kendaraan'))
                  ->orWhereHas('kendaraan', function ($k) use ($kendaraanUser, $cleanPlats) {
                      $k->whereIn('plat_nomor', $kendaraanUser->pluck('plat_nomor'));
                      foreach ($cleanPlats as $cp) {
                          $k->orWhereRaw("REPLACE(plat_nomor, ' ', '') = ?", [$cp]);
                      }
                  });
            })
            ->get()
            ->filter(function ($m) {
                if (!$m->paket) return false;
                return Carbon::parse($m->tanggal_mulai)->addDays($m->paket->durasi_hari)->gte(today());
            });

        $activeMembersMap = [];
        foreach ($activeMembersAtThisLocation as $m) {
            $activeMembersMap[$m->id_kendaraan] = $m;
            $matched = $kendaraanUser->first(function ($ku) use ($m) {
                $cp1 = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($ku->plat_nomor));
                $cp2 = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($m->kendaraan?->plat_nomor ?? ''));
                return $cp1 === $cp2;
            });
            if ($matched) {
                $activeMembersMap[$matched->id_kendaraan] = $m;
            }
        }

        return view('pengendara.katalog.show', [
            'tempat'                   => $tempatParkir,
            'kendaraanUser'            => $kendaraanUser,
            'terisiInap'               => $terisiInap,
            'sisaInap'                 => $sisaInap,
            'activeMembersByKendaraan' => $activeMembersMap,
        ]);
    }

    /**
     * Beli Paket Langganan Member (Auto-Lunas Instan via upload QRIS).
     */
    public function beliMember(Request $request, TempatParkir $tempatParkir): RedirectResponse
    {
        abort_unless($tempatParkir->status_aktif && $tempatParkir->sediakan_member, 403, 'Layanan member tidak aktif di lokasi ini.');

        $pengendara = auth('pengendara')->user();

        $request->validate([
            'id_paket'       => ['required', 'exists:paket_langganan,id_paket'],
            'id_kendaraan'   => ['required', 'exists:kendaraan,id_kendaraan'],
            'tanggal_mulai'  => ['required', 'date', 'after_or_equal:today'],
            'bukti_bayar'    => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $paket = PaketLangganan::where('id_paket', $request->id_paket)
            ->where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('status_aktif', 1)
            ->firstOrFail();

        $kendaraan = Kendaraan::where('id_kendaraan', $request->id_kendaraan)
            ->where('id_pengendara', $pengendara->id_pengendara)
            ->firstOrFail();

        if ($kendaraan->jenis_kendaraan !== $paket->jenis_kendaraan) {
            return back()->withErrors([
                'id_kendaraan' => "Kendaraan ini ({$kendaraan->jenis_kendaraan}) tidak sesuai dengan jenis kendaraan pada paket ({$paket->jenis_kendaraan})."
            ])->withInput();
        }

        // Cek apakah kendaraan sudah memiliki langganan aktif di tempat parkir INI
        $cleanPlat = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($kendaraan->plat_nomor));
        $activeMember = LanggananMember::with(['paket', 'kendaraan'])
            ->where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('status_langganan', 'Aktif')
            ->whereIn('status_pembayaran', ['Lunas', 'Menunggu Verifikasi'])
            ->where(function ($q) use ($kendaraan, $cleanPlat) {
                $q->where('id_kendaraan', $kendaraan->id_kendaraan)
                  ->orWhereHas('kendaraan', function ($k) use ($kendaraan, $cleanPlat) {
                      $k->where('plat_nomor', $kendaraan->plat_nomor)
                        ->orWhere('plat_nomor', $cleanPlat)
                        ->orWhereRaw("REPLACE(plat_nomor, ' ', '') = ?", [$cleanPlat]);
                  });
            })
            ->get()
            ->first(function ($m) {
                if (!$m->paket) return false;
                return Carbon::parse($m->tanggal_mulai)->addDays($m->paket->durasi_hari)->gte(today());
            });

        if ($activeMember) {
            $tglBerakhir = Carbon::parse($activeMember->tanggal_mulai)->addDays($activeMember->paket->durasi_hari)->translatedFormat('d F Y');
            return back()->withErrors([
                'id_kendaraan' => "Kendaraan dengan plat {$kendaraan->plat_nomor} saat ini masih memiliki status langganan member aktif di {$tempatParkir->nama_tempat} (berlaku hingga {$tglBerakhir}). Kendaraan tidak dapat berlangganan lebih dari satu paket di tempat parkir yang sama, namun Anda dapat mendaftarkannya di lokasi tempat parkir lainnya."
            ])->withInput();
        }

        // Upload bukti pembayaran QRIS
        $path = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
        $storedProof = 'storage/' . $path;
        $kodeMember = 'MBR-' . strtoupper(Str::random(4)) . '-' . mt_rand(1000, 9999);

        // Optimistic Check-In: Status langsung 'Lunas' & 'Aktif' (Auto-Lunas Instan), id_user_verifikasi NULL (Post-Audit Admin)
        LanggananMember::create([
            'kode_member'         => $kodeMember,
            'id_tempat_parkir'    => $tempatParkir->id_tempat_parkir,
            'id_paket'            => $paket->id_paket,
            'id_pengendara'       => $pengendara->id_pengendara,
            'id_kendaraan'        => $kendaraan->id_kendaraan,
            'id_user_verifikasi'  => null,
            'tanggal_mulai'       => $request->tanggal_mulai,
            'biaya_langganan'     => $paket->harga_paket,
            'bukti_bayar'         => $storedProof,
            'waktu_unggah_bukti'  => now(),
            'status_pembayaran'   => 'Lunas',
            'status_langganan'    => 'Aktif',
            'waktu_registrasi'    => now(),
        ]);

        return redirect()->route('pengendara.dashboard')
            ->with('success', "Pembelian Member Pass [{$kodeMember}] berhasil! Status langsung Lunas (Auto-Lunas Instan). Gunakan plat nomor saat check-in gerbang.");
    }

    /**
     * Booking Slot Parkir Inap (Auto-Lunas Instan via upload QRIS dengan DB::transaction).
     */
    public function bookingInap(Request $request, TempatParkir $tempatParkir): RedirectResponse
    {
        abort_unless($tempatParkir->status_aktif && $tempatParkir->sediakan_booking_inap, 403, 'Layanan booking inap tidak aktif di lokasi ini.');

        $pengendara = auth('pengendara')->user();

        $request->validate([
            'id_kendaraan'         => ['required', 'exists:kendaraan,id_kendaraan'],
            'waktu_mulai_inap'     => ['required', 'date', 'after_or_equal:now'],
            'waktu_rencana_keluar' => ['required', 'date', 'after:waktu_mulai_inap'],
            'bukti_bayar'          => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $kendaraan = Kendaraan::where('id_kendaraan', $request->id_kendaraan)
            ->where('id_pengendara', $pengendara->id_pengendara)
            ->firstOrFail();

        // Ambil tarif inap harian untuk kendaraan ini
        $tarif = Tarif::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
            ->where('jenis_kendaraan', $kendaraan->jenis_kendaraan)
            ->where('status_aktif', 1)
            ->first();

        $tarifInapHarian = (float) ($tarif?->tarif_inap_harian ?? 0);
        if ($tarifInapHarian <= 0) {
            return back()->withErrors([
                'id_kendaraan' => 'Skema tarif inap harian untuk jenis kendaraan ini belum ditetapkan oleh pengelola.'
            ])->withInput();
        }

        $mulai = Carbon::parse($request->waktu_mulai_inap);
        $keluar = Carbon::parse($request->waktu_rencana_keluar);

        // Perhitungan berbasis pergantian tengah malam (pukul 00:00 / per malam)
        $tglMulai = $mulai->copy()->startOfDay();
        $tglKeluar = $keluar->copy()->startOfDay();
        $jumlahMalam = (int) $tglMulai->diffInDays($tglKeluar);

        // Aturan Parkir Inap: Minimal 2 malam ke atas
        if ($jumlahMalam < 2) {
            return back()->withErrors([
                'waktu_rencana_keluar' => 'Layanan booking inap hanya berlaku untuk pemesanan minimal 2 malam (melewati minimal 2x pergantian tengah malam 00:00). Untuk durasi 1 malam atau parkir harian biasa, silakan langsung datang dan gunakan karcis reguler di gerbang masuk.'
            ])->withInput();
        }

        $totalBiaya = $jumlahMalam * $tarifInapHarian;

        // Gunakan DB::transaction() dengan lockForUpdate() saat memeriksa dan mengalokasikan kuota inap
        return DB::transaction(function () use ($tempatParkir, $pengendara, $kendaraan, $mulai, $keluar, $totalBiaya, $request) {
            $tp = TempatParkir::where('id_tempat_parkir', $tempatParkir->id_tempat_parkir)
                ->lockForUpdate()
                ->firstOrFail();

            $terisiInap = Booking::where('id_tempat_parkir', $tp->id_tempat_parkir)
                ->whereIn('status', ['Terjadwal', 'Aktif_Inap'])
                ->lockForUpdate()
                ->count();

            if ($terisiInap >= $tp->kuota_inap) {
                return back()->withErrors([
                    'kuota' => 'Maaf, kuota slot parkir inap di lokasi ini telah penuh.'
                ])->withInput();
            }

            // Upload bukti bayar QRIS
            $path = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
            $storedProof = 'storage/' . $path;
            $kodeBooking = 'BKG-' . strtoupper(Str::random(4)) . '-' . mt_rand(1000, 9999);

            Booking::create([
                'kode_booking'        => $kodeBooking,
                'id_tempat_parkir'    => $tp->id_tempat_parkir,
                'id_pengendara'       => $pengendara->id_pengendara,
                'id_kendaraan'        => $kendaraan->id_kendaraan,
                'id_user_verifikasi'  => null, // Post-Audit Admin
                'waktu_booking'       => now(),
                'waktu_mulai_inap'    => $mulai,
                'waktu_rencana_keluar'=> $keluar,
                'biaya_inap_dibayar'  => $totalBiaya,
                'bukti_bayar'         => $storedProof,
                'waktu_unggah_bukti'  => now(),
                'status_pembayaran'   => 'Lunas', // Auto-Lunas Instan
                'status'              => 'Terjadwal',
            ]);

            return redirect()->route('pengendara.dashboard')
                ->with('success', "Booking Parkir Inap [{$kodeBooking}] berhasil! Pembayaran berstatus Lunas (Auto-Lunas Instan). Slot Anda telah diamankan.");
        });
    }
}
