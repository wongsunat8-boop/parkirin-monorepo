<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KapasitasController extends Controller
{
    private function tenant(): TempatParkir
    {
        $tenant = TempatParkir::findOrFail(Auth::guard('web')->user()->id_tempat_parkir);
        return $tenant;
    }

    public function index()
    {
        $tenant = $this->tenant();
        return view('admin.kapasitas.index', compact('tenant'));
    }

    public function store(Request $request)
    {
        $tenant = $this->tenant();
        if ($tenant->kapasitas > 0) {
            return back()->with('error', 'Kapasitas sudah diatur. Gunakan Edit untuk mengubah nilai.');
        }
        $data = $request->validate(['kapasitas' => 'required|integer|min:1']);
        $tenant->update(['kapasitas' => $data['kapasitas'], 'terisi' => 0]);
        return back()->with('success', 'Kapasitas awal berhasil ditetapkan.');
    }

    public function update(Request $request)
    {
        $tenant = $this->tenant();
        $data = $request->validate([
            'kapasitas' => 'required|integer|min:0',
            'terisi' => 'required|integer|min:0',
        ]);
        if ($data['terisi'] > $data['kapasitas']) {
            return back()->withErrors(['terisi' => 'Terisi tidak boleh melebihi kapasitas.'])->withInput();
        }
        $tenant->update($data);
        return back()->with('success', 'Kapasitas berhasil diperbarui.');
    }

    public function toggleBooking()
    {
        $tenant = $this->tenant();
        $tenant->update(['sediakan_booking' => $tenant->sediakan_booking ? 0 : 1]);
        return back()->with('success', 'Pengaturan booking diperbarui.');
    }
}
