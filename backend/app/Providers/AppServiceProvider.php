<?php

namespace App\Providers;

use App\Models\Tarif;
use App\Models\TempatParkir;
use App\Models\Transaksi;
use App\Observers\TarifObserver;
use App\Observers\TempatParkirObserver;
use App\Observers\TransaksiObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TempatParkir::observe(TempatParkirObserver::class);
        Tarif::observe(TarifObserver::class);
        Transaksi::observe(TransaksiObserver::class);

        // Registrasi function fn_hitung_biaya_parkir untuk SQLite saat running test
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            try {
                $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
                if (method_exists($pdo, 'sqliteCreateFunction')) {
                    $pdo->sqliteCreateFunction('fn_hitung_biaya_parkir', function ($idTarif, $durasiJam, $jumlahMalam = 0) {
                        $tarif = \App\Models\Tarif::find($idTarif);
                        if (!$tarif) return 0.0;
                        $durasi = max((int) $durasiJam, 1);
                        $malam = max((int) $jumlahMalam, 0);
                        $tipe = $tarif->tipe_tarif;
                        $perJam = (float) $tarif->tarif_per_jam;
                        $jamPertama = (float) $tarif->tarif_jam_pertama;
                        $berikutnya = (float) $tarif->tarif_berikutnya;
                        $inap = (float) $tarif->tarif_inap_harian;

                        if ($tipe === 'Per Jam') {
                            if ($jamPertama > 0) {
                                $total = $jamPertama;
                                if ($durasi > 1) {
                                    $total += ($durasi - 1) * ($berikutnya > 0 ? $berikutnya : $perJam);
                                }
                                return $total;
                            }
                            return $durasi * $perJam;
                        }
                        if ($tipe === 'Flat Harian') {
                            if ($malam === 0 && $durasi >= 24) {
                                $malam = (int) floor($durasi / 24);
                            }
                            if ($malam > 0) {
                                return $inap > 0 ? ($malam * $inap) : (($malam + 1) * $perJam);
                            }
                            return $perJam;
                        }
                        if ($tipe === 'Progresif Mal') {
                            $total = $jamPertama > 0 ? $jamPertama : $perJam;
                            if ($durasi > 1) {
                                $total += ($durasi - 1) * ($berikutnya > 0 ? $berikutnya : $perJam);
                            }
                            return $total;
                        }
                        return $durasi * $perJam;
                    });
                }
            } catch (\Throwable $e) {}
        }
    }
}
