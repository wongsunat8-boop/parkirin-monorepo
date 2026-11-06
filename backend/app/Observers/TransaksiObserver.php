<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TransaksiObserver
{
    private function getAuthUser(): ?User
    {
        $user = Auth::user();
        return $user instanceof User ? $user : null;
    }

    public function created($trx): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        LogAktivitas::create([
            'id_tempat_parkir' => $trx->id_tempat_parkir,
            'id_user'          => $user->id_user,
            'aktivitas'        => "Transaksi masuk: kendaraan #{$trx->id_kendaraan}",
            'waktu_aktivitas'  => now(),
        ]);
    }

    public function updated($trx): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        if ($trx->wasChanged('status') && $trx->status === 'keluar') {
            LogAktivitas::create([
                'id_tempat_parkir' => $trx->id_tempat_parkir,
                'id_user'          => $user->id_user,
                'aktivitas'        => "Transaksi keluar #{$trx->id_transaksi}: biaya Rp{$trx->biaya_total}",
                'waktu_aktivitas'  => now(),
            ]);
        }
    }
}

