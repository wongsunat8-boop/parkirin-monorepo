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
    }
}
