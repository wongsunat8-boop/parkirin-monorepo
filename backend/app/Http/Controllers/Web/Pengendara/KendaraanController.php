<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\PengajuanKlaimKendaraan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    private function normalizePlat(string $plat): string
    {
        return strtoupper(preg_replace('/\s+/', '', $plat));
    }

    public function store(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:15'],
            'jenis_kendaraan' => ['required', 'in:Motor,Mobil,Sepeda'],
            'warna' => ['required', 'string', 'max:20'],
        ]);
        $plat = $this->normalizePlat($validated['plat_nomor']);

        $existing = Kendaraan::where('plat_nomor', $plat)->first();
        if ($existing) {
            if ($existing->id_pengendara === $pengendara->id_pengendara) {
                return back()->with('error', 'Kendaraan dengan plat tersebut sudah ada di akun Anda.');
            }
            if ($existing->id_pengendara) {
                return back()->with('error', 'Plat nomor sudah terdaftar pada akun lain. Gunakan "Ajukan Klaim Kendaraan".');
            }
            // Kendaraan ada (offline/unlinked) - tautkan ke pengendara
            $existing->update(['id_pengendara' => $pengendara->id_pengendara, 'warna' => $validated['warna']]);
            return back()->with('success', 'Kendaraan ditemukan dan berhasil ditautkan ke akun Anda.');
        }

        Kendaraan::create([
            'plat_nomor' => $plat,
            'jenis_kendaraan' => $validated['jenis_kendaraan'],
            'warna' => $validated['warna'],
            'id_pengendara' => $pengendara->id_pengendara,
        ]);
        return back()->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($kendaraan->id_pengendara !== $pengendara->id_pengendara) abort(403);

        $locked = $kendaraan->hasTransactionHistory();
        $rules = ['warna' => ['required', 'string', 'max:20']];
        if (!$locked) {
            $rules['plat_nomor'] = ['required', 'string', 'max:15'];
            $rules['jenis_kendaraan'] = ['required', 'in:Motor,Mobil,Sepeda'];
        }
        $validated = $request->validate($rules);

        $data = ['warna' => $validated['warna']];
        if (!$locked) {
            $plat = $this->normalizePlat($validated['plat_nomor']);
            $conflict = Kendaraan::where('plat_nomor', $plat)->where('id_kendaraan', '!=', $kendaraan->id_kendaraan)->exists();
            if ($conflict) return back()->with('error', 'Plat nomor sudah digunakan kendaraan lain.');
            $data['plat_nomor'] = $plat;
            $data['jenis_kendaraan'] = $validated['jenis_kendaraan'];
        }
        $kendaraan->update($data);
        return back()->with('success', $locked
            ? 'Warna kendaraan diperbarui. Plat nomor & jenis dikunci karena memiliki riwayat transaksi (Immutable History).'
            : 'Kendaraan berhasil diperbarui.');
    }

    public function unlink(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        if ($kendaraan->id_pengendara !== $pengendara->id_pengendara) abort(403);

        if ($kendaraan->hasTransactionHistory()) {
            $kendaraan->update(['id_pengendara' => null]);
            return back()->with('success', 'Kendaraan dilepas dari akun (riwayat transaksi tetap tersimpan).');
        }
        $kendaraan->delete();
        return back()->with('success', 'Kendaraan dihapus dari akun Anda.');
    }

    public function ajukanKlaim(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        $validated = $request->validate(['plat_nomor' => ['required', 'string', 'max:15']]);
        $plat = $this->normalizePlat($validated['plat_nomor']);

        $kendaraan = Kendaraan::where('plat_nomor', $plat)->first();
        if (!$kendaraan) {
            return back()->with('error', 'Data kendaraan dengan plat tersebut tidak ditemukan.');
        }
        if ($kendaraan->id_pengendara === $pengendara->id_pengendara) {
            return back()->with('error', 'Kendaraan ini sudah terdaftar pada akun Anda.');
        }
        if (!$kendaraan->id_pengendara) {
            $kendaraan->update(['id_pengendara' => $pengendara->id_pengendara]);
            return back()->with('success', 'Kendaraan tidak memiliki pemilik aktif dan langsung ditautkan ke akun Anda.');
        }
        $exists = PengajuanKlaimKendaraan::where('id_kendaraan', $kendaraan->id_kendaraan)
            ->where('id_pengaju', $pengendara->id_pengendara)
            ->where('status_klaim', 'Pending')->exists();
        if ($exists) {
            return back()->with('error', 'Anda sudah memiliki pengajuan klaim Pending untuk kendaraan ini.');
        }

        PengajuanKlaimKendaraan::create([
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_pengaju' => $pengendara->id_pengendara,
            'id_pengendara_target' => $kendaraan->id_pengendara,
            'status_klaim' => 'Pending',
            'tanggal_pengajuan' => now(),
        ]);
        return back()->with('success', 'Pengajuan klaim kendaraan terkirim. Menunggu persetujuan pemilik saat ini.');
    }
}
