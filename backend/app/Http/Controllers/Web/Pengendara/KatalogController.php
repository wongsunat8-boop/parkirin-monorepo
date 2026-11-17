<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(): View
    {
        $tempat = TempatParkir::with(['tarif' => fn ($q) => $q->where('status_aktif', 1)->orderBy('jenis_kendaraan')])
            ->where('status_aktif', 1)
            ->orderBy('nama_tempat')
            ->get();

        return view('pengendara.katalog.index', compact('tempat'));
    }

    public function show(TempatParkir $tempatParkir): View
    {
        abort_unless($tempatParkir->status_aktif, 404);

        $tempatParkir->load(['tarif' => fn ($q) => $q->where('status_aktif', 1)->orderBy('jenis_kendaraan')]);

        return view('pengendara.katalog.show', [
            'tempat' => $tempatParkir,
        ]);
    }
}
