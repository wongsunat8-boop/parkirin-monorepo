<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\LanggananMember;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifikasiPembayaranController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $tab = $request->query('tab', 'unverified'); // 'unverified', 'member', 'booking'
        $search = $request->query('q');

        // Unverified counts for Alert Toast / Badges
        $unverifiedMemberCount = LanggananMember::where('id_tempat_parkir', $tenantId)
            ->whereNull('id_user_verifikasi')
            ->whereNotNull('bukti_bayar')
            ->count();

        $unverifiedBookingCount = Booking::where('id_tempat_parkir', $tenantId)
            ->whereNull('id_user_verifikasi')
            ->whereNotNull('bukti_bayar')
            ->count();

        $totalUnverified = $unverifiedMemberCount + $unverifiedBookingCount;

        // Query Members
        $memberQuery = LanggananMember::with([
                'paket:id_paket,nama_paket,jenis_kendaraan,harga_paket,durasi_hari',
                'pengendara:id_pengendara,nama_lengkap,email,no_telepon',
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'userVerifikasi:id_user,nama_lengkap',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->when($tab === 'unverified', fn($q) => $q->whereNull('id_user_verifikasi'))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_member', 'like', "%{$search}%")
                        ->orWhereHas('pengendara', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('kendaraan', fn($k) => $k->where('plat_nomor', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id_langganan');

        // Query Bookings
        $bookingQuery = Booking::with([
                'pengendara:id_pengendara,nama_lengkap,email,no_telepon',
                'kendaraan:id_kendaraan,plat_nomor,jenis_kendaraan',
                'userVerifikasi:id_user,nama_lengkap',
            ])
            ->where('id_tempat_parkir', $tenantId)
            ->when($tab === 'unverified', fn($q) => $q->whereNull('id_user_verifikasi'))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_booking', 'like', "%{$search}%")
                        ->orWhereHas('pengendara', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('kendaraan', fn($k) => $k->where('plat_nomor', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id_booking');

        $members = $memberQuery->paginate(10, ['*'], 'member_page')->withQueryString();
        $bookings = $bookingQuery->paginate(10, ['*'], 'booking_page')->withQueryString();

        return view('admin.verifikasi_pembayaran.index', compact(
            'tab',
            'search',
            'members',
            'bookings',
            'unverifiedMemberCount',
            'unverifiedBookingCount',
            'totalUnverified'
        ));
    }

    public function verifikasiMember(LanggananMember $langganan)
    {
        $tenantId = $this->tenantId();
        abort_unless((int) $langganan->id_tempat_parkir === $tenantId, 403, 'Akses ditolak.');

        $langganan->update([
            'status_pembayaran' => 'Lunas',
            'status_langganan'  => 'Aktif',
            'id_user_verifikasi' => Auth::id(),
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenantId,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Verifikasi Pembayaran Member Pass [{$langganan->kode_member}] disetujui (Lunas)",
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('success', "Pembayaran Member Pass {$langganan->kode_member} berhasil diverifikasi (Lunas).");
    }

    public function tolakMember(Request $request, LanggananMember $langganan)
    {
        $tenantId = $this->tenantId();
        abort_unless((int) $langganan->id_tempat_parkir === $tenantId, 403, 'Akses ditolak.');

        $langganan->update([
            'status_pembayaran' => 'Ditolak',
            'status_langganan'  => 'Dibatalkan',
            'id_user_verifikasi' => Auth::id(),
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenantId,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Cek Pembayaran: Pembayaran Member [{$langganan->kode_member}] ditolak karena bukti tidak sesuai",
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('error', "Pembayaran Member Pass {$langganan->kode_member} ditolak. Langganan dibatalkan.");
    }

    public function verifikasiBooking(Booking $booking)
    {
        $tenantId = $this->tenantId();
        abort_unless((int) $booking->id_tempat_parkir === $tenantId, 403, 'Akses ditolak.');

        $booking->update([
            'status_pembayaran' => 'Lunas',
            'id_user_verifikasi' => Auth::id(),
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenantId,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Verifikasi Pembayaran Booking [{$booking->kode_booking}] disetujui (Lunas)",
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('success', "Pembayaran Booking {$booking->kode_booking} berhasil diverifikasi (Lunas).");
    }

    public function tolakBooking(Request $request, Booking $booking)
    {
        $tenantId = $this->tenantId();
        abort_unless((int) $booking->id_tempat_parkir === $tenantId, 403, 'Akses ditolak.');

        $booking->update([
            'status_pembayaran' => 'Ditolak',
            'status'            => 'Batal',
            'id_user_verifikasi' => Auth::id(),
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenantId,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Cek Pembayaran: Pembayaran Booking [{$booking->kode_booking}] ditolak karena bukti tidak sesuai",
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('error', "Pembayaran Booking {$booking->kode_booking} ditolak. Status booking dibatalkan.");
    }
}
