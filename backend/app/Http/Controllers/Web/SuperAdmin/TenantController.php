<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = TempatParkir::query()
            ->withCount(['users'])
            ->when($request->filled('q'), fn($q) => $q->where('nama_tempat', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn($q) => $q->where('status_aktif', $request->status))
            ->orderByDesc('id_tempat_parkir')
            ->paginate(15)
            ->withQueryString();

        return view('super_admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('super_admin.tenants.form', ['tenant' => new TempatParkir()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_tempat' => 'required|string|max:100',
            'kapasitas' => 'nullable|integer|min:0',
            'sediakan_booking' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);
        TempatParkir::create([
            'nama_tempat' => $data['nama_tempat'],
            'kapasitas' => $data['kapasitas'] ?? 0,
            'terisi' => 0,
            'sediakan_booking' => $request->boolean('sediakan_booking', true) ? 1 : 0,
            'status_aktif' => $request->boolean('status_aktif', true) ? 1 : 0,
        ]);

        return redirect()->route('super-admin.tenants.index')->with('success', 'Tenant berhasil ditambahkan.');
    }

    public function edit(TempatParkir $tenant)
    {
        return view('super_admin.tenants.form', compact('tenant'));
    }

    public function update(Request $request, TempatParkir $tenant)
    {
        $data = $request->validate([
            'nama_tempat' => 'required|string|max:100',
            'sediakan_booking' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);
        $tenant->update([
            'nama_tempat' => $data['nama_tempat'],
            'sediakan_booking' => $request->boolean('sediakan_booking') ? 1 : 0,
            'status_aktif' => $request->boolean('status_aktif') ? 1 : 0,
        ]);

        return redirect()->route('super-admin.tenants.index')->with('success', 'Tenant berhasil diperbarui.');
    }

    public function toggle(TempatParkir $tenant)
    {
        $tenant->update(['status_aktif' => $tenant->status_aktif ? 0 : 1]);
        return back()->with('success', 'Status tenant diperbarui.');
    }
}
