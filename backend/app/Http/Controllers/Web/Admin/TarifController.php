<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TarifController extends Controller
{
    private function tenantId(): int { return (int) Auth::guard('web')->user()->id_tempat_parkir; }

    public function index()
    {
        $tarifs = Tarif::where('id_tempat_parkir', $this->tenantId())
            ->orderBy('jenis_kendaraan')
            ->paginate(15);
        return view('admin.tarif.index', compact('tarifs'));
    }

    public function create()
    {
        return view('admin.tarif.form', ['tarif' => new Tarif()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['id_tempat_parkir'] = $this->tenantId();
        $data['status_aktif'] = $request->boolean('status_aktif', true) ? 1 : 0;
        Tarif::create($data);
        return redirect()->route('admin.tarif.index')->with('success', 'Tarif berhasil ditambahkan.');
    }

    public function edit(Tarif $tarif)
    {
        abort_unless($tarif->id_tempat_parkir === $this->tenantId(), 404);
        return view('admin.tarif.form', compact('tarif'));
    }

    public function update(Request $request, Tarif $tarif)
    {
        abort_unless($tarif->id_tempat_parkir === $this->tenantId(), 404);
        $data = $this->validated($request, $tarif->id_tarif);
        $data['status_aktif'] = $request->boolean('status_aktif') ? 1 : 0;
        $tarif->update($data);
        return redirect()->route('admin.tarif.index')->with('success', 'Tarif berhasil diperbarui.');
    }

    public function destroy(Tarif $tarif)
    {
        abort_unless($tarif->id_tempat_parkir === $this->tenantId(), 404);
        $tarif->delete();
        return back()->with('success', 'Tarif dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'jenis_kendaraan' => ['required', Rule::in(['Motor','Mobil','Sepeda'])],
            'tipe_tarif' => ['required', Rule::in(['Per Jam','Flat Harian','Progresif Mal'])],
            'tarif_per_jam' => 'nullable|numeric|min:0',
            'tarif_jam_pertama' => 'nullable|numeric|min:0',
            'tarif_berikutnya' => 'nullable|numeric|min:0',
            'tarif_inap' => 'nullable|numeric|min:0',
            'status_aktif' => 'nullable|boolean',
        ]);
        // Uniqueness per tenant+jenis_kendaraan
        $exists = Tarif::where('id_tempat_parkir', $this->tenantId())
            ->where('jenis_kendaraan', $data['jenis_kendaraan'])
            ->when($ignoreId, fn($q) => $q->where('id_tarif','!=',$ignoreId))
            ->exists();
        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jenis_kendaraan' => 'Tarif untuk jenis kendaraan ini sudah ada di tenant Anda.',
            ]);
        }
        foreach (['tarif_per_jam','tarif_jam_pertama','tarif_berikutnya','tarif_inap'] as $f) $data[$f] = $data[$f] ?? 0;
        return $data;
    }
}
