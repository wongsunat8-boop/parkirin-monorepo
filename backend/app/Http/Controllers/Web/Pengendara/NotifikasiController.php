<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\PengajuanKlaimKendaraan;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(): View
    {
        $pengendara = auth('pengendara')->user();

        // 1. Klaim Kendaraan Masuk (Pengajuan dari pihak lain untuk kendaraan milik pengendara ini)
        $masuk = PengajuanKlaimKendaraan::with(['kendaraan', 'pengaju'])
            ->where('id_pengendara_target', $pengendara->id_pengendara)
            ->orderByRaw("FIELD(status_klaim,'Pending','Disetujui','Ditolak','Dibatalkan')")
            ->orderByDesc('tanggal_pengajuan')
            ->get();

        // 2. Riwayat Klaim yang Diajukan oleh Pengendara ini
        $diajukan = PengajuanKlaimKendaraan::with(['kendaraan', 'pengendaraTarget'])
            ->where('id_pengaju', $pengendara->id_pengendara)
            ->orderByDesc('tanggal_pengajuan')
            ->get();

        // 3. Notifikasi Umum (Pembaruan Sistem, Booking Parkir Mobile/Web, Keamanan & Konfirmasi OTP)
        $notifikasiUmum = collect();

        // 3.A. Booking Parkir (Mendukung data dari Mobile Apps & Web)
        $bookings = Booking::with(['tempatParkir', 'kendaraan'])
            ->where('id_pengendara', $pengendara->id_pengendara)
            ->orderByDesc('waktu_booking')
            ->limit(10)
            ->get();

        foreach ($bookings as $b) {
            $statusLabel = match ($b->status) {
                'Menunggu' => 'Menunggu Kedatangan',
                'Selesai'  => 'Selesai',
                'Batal'    => 'Dibatalkan',
                default    => $b->status,
            };

            $statusColor = match ($b->status) {
                'Menunggu' => 'amber',
                'Selesai'  => 'emerald',
                'Batal'    => 'rose',
                default    => 'slate',
            };

            $notifikasiUmum->push((object) [
                'id'           => 'booking-' . $b->id_booking,
                'kategori'     => 'booking',
                'judul'        => 'Reservasi Parkir: ' . ($b->tempatParkir?->nama_tempat ?? 'Lokasi Parkir'),
                'pesan'        => 'Kode Reservasi #' . $b->kode_booking . ' untuk kendaraan ' . ($b->kendaraan?->plat_nomor ?? '-') . ' (' . ($b->kendaraan?->jenis_kendaraan ?? 'Kendaraan') . '). Rencana kedatangan: ' . ($b->waktu_rencana_kedatangan ? $b->waktu_rencana_kedatangan->format('d M Y, H:i') : '-') . '.',
                'waktu'        => $b->waktu_booking ? Carbon::parse($b->waktu_booking) : now(),
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'icon_type'    => 'calendar',
                'action_url'   => route('pengendara.katalog.index'),
                'action_label' => 'Lihat Lokasi',
            ]);
        }

        // 3.B. Aktivitas Tiket Transaksi Parkir Terbaru
        $transaksi = Transaksi::with(['tempatParkir', 'kendaraan'])
            ->where('id_pengendara', $pengendara->id_pengendara)
            ->orderByDesc('waktu_masuk')
            ->limit(5)
            ->get();

        foreach ($transaksi as $t) {
            $isKeluar = ($t->status === 'keluar');
            $notifikasiUmum->push((object) [
                'id'           => 'transaksi-' . $t->id_transaksi,
                'kategori'     => 'booking',
                'judul'        => 'Aktivitas Parkir: ' . ($t->tempatParkir?->nama_tempat ?? 'Lokasi Parkir'),
                'pesan'        => 'Kendaraan ' . ($t->kendaraan?->plat_nomor ?? '-') . ' tercatat ' . ($isKeluar ? 'telah keluar dari' : 'sedang parkir di') . ' ' . ($t->tempatParkir?->nama_tempat ?? 'area parkir') . '.' . ($t->biaya_total ? ' Total biaya: Rp ' . number_format($t->biaya_total, 0, ',', '.') : ''),
                'waktu'        => $t->waktu_keluar ? Carbon::parse($t->waktu_keluar) : ($t->waktu_masuk ? Carbon::parse($t->waktu_masuk) : now()),
                'status_label' => $isKeluar ? 'Parkir Selesai' : 'Sedang Parkir',
                'status_color' => $isKeluar ? 'emerald' : 'blue',
                'icon_type'    => 'car',
                'action_url'   => route('pengendara.riwayat.index'),
                'action_label' => 'Lihat Riwayat',
            ]);
        }

        // 3.C. Notifikasi Keamanan & Konfirmasi OTP WhatsApp
        if (!empty($pengendara->no_telepon)) {
            $notifikasiUmum->push((object) [
                'id'           => 'keamanan-otp-active',
                'kategori'     => 'keamanan',
                'judul'        => 'Verifikasi OTP WhatsApp Aktif',
                'pesan'        => 'Nomor telepon ' . $pengendara->no_telepon . ' berhasil ditautkan dan siap menerima kode OTP serta notifikasi tiket secara otomatis.',
                'waktu'        => now()->subHours(2),
                'status_label' => 'Terhubung',
                'status_color' => 'emerald',
                'icon_type'    => 'phone',
                'action_url'   => route('pengendara.profil.index'),
                'action_label' => 'Kelola Profil',
            ]);
        } else {
            $notifikasiUmum->push((object) [
                'id'           => 'keamanan-otp-missing',
                'kategori'     => 'keamanan',
                'judul'        => 'Nomor Telepon OTP Belum Terdaftar',
                'pesan'        => 'Akun Anda belum memiliki nomor telepon aktif. Lengkapi nomor WhatsApp pada profil untuk mengaktifkan kode OTP dan konfirmasi reservasi mobile.',
                'waktu'        => now()->subHours(1),
                'status_label' => 'Perhatian',
                'status_color' => 'amber',
                'icon_type'    => 'phone',
                'action_url'   => route('pengendara.profil.index'),
                'action_label' => 'Atur Nomor',
            ]);
        }

        // 3.D. Kebijakan Skor Reputasi Akun
        $reputasi = (int) ($pengendara->skor_reputasi ?? 100);
        $notifikasiUmum->push((object) [
            'id'           => 'keamanan-reputasi',
            'kategori'     => 'keamanan',
            'judul'        => 'Skor Reputasi Akun: ' . $reputasi . '/100',
            'pesan'        => $reputasi >= 80 
                ? 'Status akun Anda sangat baik. Tetap patuhi tata tertib parkir dan selesaikan reservasi tepat waktu untuk mempertahankan kemudahan akses booking.' 
                : 'Skor reputasi akun Anda mengalami pengurangan. Hindari pembatalan reservasi mendadak agar akun tidak terkena pembatasan.',
            'waktu'        => now()->subDays(1),
            'status_label' => $reputasi >= 80 ? 'Status Baik' : 'Peringatan',
            'status_color' => $reputasi >= 80 ? 'blue' : 'rose',
            'icon_type'    => 'shield',
            'action_url'   => null,
            'action_label' => null,
        ]);

        // 3.E. Pembaruan Sistem Resmi Parkirin
        $notifikasiUmum->push((object) [
            'id'           => 'sistem-release-2-0',
            'kategori'     => 'sistem',
            'judul'        => 'Pembaruan Sistem Parkirin v2.0 Telah Rilis',
            'pesan'        => 'Pembaruan terbaru menghadirkan antarmuka Bento Grid responsif, integrasi kode QR reservasi mobile, serta notifikasi instan pemindahan kepemilikan kendaraan.',
            'waktu'        => now()->subDays(2),
            'status_label' => 'Pembaruan',
            'status_color' => 'blue',
            'icon_type'    => 'sparkles',
            'action_url'   => null,
            'action_label' => null,
        ]);

        $notifikasiUmum->push((object) [
            'id'           => 'sistem-maintenance-info',
            'kategori'     => 'sistem',
            'judul'        => 'Peningkatan Keamanan & Keandalan Gateway OTP',
            'pesan'        => 'Sistem pemantauan slot dan gateway WhatsApp OTP telah ditingkatkan untuk memastikan seluruh pesan verifikasi terkirim tepat waktu dalam hitungan detik.',
            'waktu'        => now()->subDays(4),
            'status_label' => 'Informasi',
            'status_color' => 'slate',
            'icon_type'    => 'bell',
            'action_url'   => null,
            'action_label' => null,
        ]);

        // Urutkan dari notifikasi paling baru
        $notifikasiUmum = $notifikasiUmum->sortByDesc('waktu')->values();

        // Hitung statistik untuk KPI Bento Header
        $counts = [
            'total_umum'    => $notifikasiUmum->count(),
            'booking'       => $notifikasiUmum->where('kategori', 'booking')->count(),
            'keamanan'      => $notifikasiUmum->where('kategori', 'keamanan')->count(),
            'sistem'        => $notifikasiUmum->where('kategori', 'sistem')->count(),
            'klaim_pending' => $masuk->where('status_klaim', 'Pending')->count(),
            'klaim_total'   => $masuk->count() + $diajukan->count(),
        ];

        return view('pengendara.notifikasi.index', compact('masuk', 'diajukan', 'notifikasiUmum', 'counts'));
    }

    public function setujui(Request $request, PengajuanKlaimKendaraan $klaim): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($klaim->id_pengendara_target !== $pengendara->id_pengendara) abort(403);
        if ($klaim->status_klaim !== 'Pending') {
            return back()->with('error', 'Klaim ini sudah diproses.');
        }

        DB::transaction(function () use ($klaim) {
            $k = PengajuanKlaimKendaraan::where('id_klaim', $klaim->id_klaim)->lockForUpdate()->first();
            if (!$k || $k->status_klaim !== 'Pending') return;
            Kendaraan::where('id_kendaraan', $k->id_kendaraan)->update(['id_pengendara' => $k->id_pengaju]);
            $k->update(['status_klaim' => 'Disetujui']);
        });

        return back()->with('success', 'Klaim disetujui. Kepemilikan kendaraan telah dipindahkan ke pengaju.');
    }

    public function tolak(Request $request, PengajuanKlaimKendaraan $klaim): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($klaim->id_pengendara_target !== $pengendara->id_pengendara) abort(403);
        if ($klaim->status_klaim !== 'Pending') {
            return back()->with('error', 'Klaim ini sudah diproses.');
        }
        $klaim->update(['status_klaim' => 'Ditolak']);
        return back()->with('success', 'Klaim kendaraan ditolak.');
    }
}
