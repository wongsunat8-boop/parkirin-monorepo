<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TempatParkirObserver
{
    private function getAuthUser(): ?User
    {
        $user = Auth::user();
        return $user instanceof User ? $user : null;
    }

    public function created($tp): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        LogAktivitas::create([
            'id_tempat_parkir' => $tp->id_tempat_parkir,
            'id_user'          => $user->id_user,
            'aktivitas'        => "Membuat tempat parkir: {$tp->nama_tempat}",
            'waktu_aktivitas'  => now(),
        ]);
    }

    public function updated($tp): void
    {
        $user = $this->getAuthUser();
        if (!$user) return;

        $changes = [];
        if ($tp->wasChanged('kapasitas')) {
            $changes[] = "kapasitas={$tp->kapasitas}";
        }
        if ($tp->wasChanged('sediakan_booking')) {
            $label = $tp->sediakan_booking ? 'aktif' : 'nonaktif';
            $changes[] = "booking={$label}";
        }
        if ($tp->wasChanged('status_aktif')) {
            $label = $tp->status_aktif ? 'aktif' : 'nonaktif';
            $changes[] = "status={$label}";
        }
        if (!empty($changes)) {
            LogAktivitas::create([
                'id_tempat_parkir' => $tp->id_tempat_parkir,
                'id_user'          => $user->id_user,
                'aktivitas'        => mb_substr("Update tempat parkir [{$tp->nama_tempat}]: " . implode(', ', $changes), 0, 150),
                'waktu_aktivitas'  => now(),
            ]);
        }
    }
}

