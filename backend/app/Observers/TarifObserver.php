<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TarifObserver
{
    private function getAuthUser(): ?User
    {
        $user = Auth::user();
        return $user instanceof User ? $user : null;
    }

    public function created($tarif): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        LogAktivitas::create([
            'id_tempat_parkir' => $tarif->id_tempat_parkir,
            'id_user'          => $user->id_user,
            'aktivitas'        => "Membuat tarif {$tarif->jenis_kendaraan} ({$tarif->tipe_tarif})",
            'waktu_aktivitas'  => now(),
        ]);
    }

    public function updated($tarif): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        LogAktivitas::create([
            'id_tempat_parkir' => $tarif->id_tempat_parkir,
            'id_user'          => $user->id_user,
            'aktivitas'        => "Update tarif {$tarif->jenis_kendaraan} ({$tarif->tipe_tarif})",
            'waktu_aktivitas'  => now(),
        ]);
    }
}

