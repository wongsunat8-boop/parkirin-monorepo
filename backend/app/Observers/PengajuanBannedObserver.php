<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PengajuanBannedObserver
{
    private function getAuthUser(): ?User
    {
        $user = Auth::user();
        return $user instanceof User ? $user : null;
    }

    public function created($banned): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        LogAktivitas::create([
            'id_tempat_parkir' => $banned->id_tempat_parkir,
            'id_user'          => $user->id_user,
            'aktivitas'        => mb_substr("Mengajukan banned: {$banned->alasan}", 0, 150),
            'waktu_aktivitas'  => now(),
        ]);
    }

    public function updated($banned): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        if ($banned->wasChanged('status_pengajuan')) {
            LogAktivitas::create([
                'id_tempat_parkir' => $banned->id_tempat_parkir,
                'id_user'          => $user->id_user,
                'aktivitas'        => "Update status banned #{$banned->id_banned}: {$banned->status_pengajuan}",
                'waktu_aktivitas'  => now(),
            ]);
        }
    }
}

