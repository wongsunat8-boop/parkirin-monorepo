<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\TempatParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        // Hybrid Pagination: Offset pagination for master table (PART 1.3)
        $tenants = TempatParkir::query()
            ->withCount(['users'])
            ->when($request->filled('q'), fn($q) => $q->where('nama_tempat', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn($q) => $q->where('status_aktif', $request->status))
            ->orderByDesc('id_tempat_parkir')
            ->paginate(10)
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
            'kuota_inap' => 'nullable|integer|min:0',
            'sediakan_booking' => 'nullable|boolean',
            'sediakan_member' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);

        $tenant = TempatParkir::create([
            'nama_tempat' => $data['nama_tempat'],
            'kapasitas_reguler' => $data['kapasitas'] ?? 0,
            'kuota_inap' => $data['kuota_inap'] ?? 0,
            'sediakan_booking_inap' => $request->boolean('sediakan_booking', true) ? 1 : 0,
            'sediakan_member' => $request->boolean('sediakan_member', true) ? 1 : 0,
            'status_aktif' => $request->boolean('status_aktif', true) ? 1 : 0,
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Super Admin: Buat Tenant Baru [{$tenant->nama_tempat}]",
            'waktu_aktivitas'  => now(),
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
            'kapasitas' => 'nullable|integer|min:0',
            'kuota_inap' => 'nullable|integer|min:0',
            'sediakan_booking' => 'nullable|boolean',
            'sediakan_member' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);

        $tenant->update([
            'nama_tempat' => $data['nama_tempat'],
            'kapasitas_reguler' => $data['kapasitas'] ?? $tenant->kapasitas_reguler,
            'kuota_inap' => $data['kuota_inap'] ?? $tenant->kuota_inap,
            'sediakan_booking_inap' => $request->boolean('sediakan_booking') ? 1 : 0,
            'sediakan_member' => $request->boolean('sediakan_member') ? 1 : 0,
            'status_aktif' => $request->boolean('status_aktif') ? 1 : 0,
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Super Admin: Perbarui Data Tenant [{$tenant->nama_tempat}]",
            'waktu_aktivitas'  => now(),
        ]);

        return redirect()->route('super-admin.tenants.index')->with('success', 'Tenant berhasil diperbarui.');
    }

    public function toggle(TempatParkir $tenant)
    {
        $tenant->update(['status_aktif' => $tenant->status_aktif ? 0 : 1]);

        LogAktivitas::create([
            'id_tempat_parkir' => $tenant->id_tempat_parkir,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Super Admin: Ubah Status Tenant [{$tenant->nama_tempat}] menjadi " . ($tenant->status_aktif ? 'Aktif' : 'Nonaktif'),
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('success', 'Status tenant diperbarui.');
    }
}
