<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\TempatParkir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        // Hybrid Pagination: Offset pagination for master table (PART 1.3)
        $owners = User::query()
            ->with('tempatParkir:id_tempat_parkir,nama_tempat')
            ->where('role', 'owner')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($qq) use ($request) {
                    $qq->where('nama_lengkap', 'like', '%'.$request->q.'%')
                       ->orWhere('username', 'like', '%'.$request->q.'%');
                });
            })
            ->orderByDesc('id_user')
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.owners.index', compact('owners'));
    }

    public function create()
    {
        $tenants = TempatParkir::where('status_aktif', 1)->orderBy('nama_tempat')->get(['id_tempat_parkir','nama_tempat']);
        $owner = new User();
        return view('super_admin.owners.form', compact('tenants', 'owner'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'username' => 'required|string|max:50|alpha_dash|unique:user,username',
            'password' => ['required', Password::min(8)],
            'id_tempat_parkir' => 'required|exists:tempat_parkir,id_tempat_parkir',
            'status_aktif' => 'nullable|boolean',
        ]);

        $owner = User::create([
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'owner',
            'id_tempat_parkir' => $data['id_tempat_parkir'],
            'status_aktif' => $request->boolean('status_aktif', true) ? 1 : 0,
        ]);

        LogAktivitas::create([
            'id_tempat_parkir' => $owner->id_tempat_parkir,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Super Admin: Buat Akun Owner [{$owner->nama_lengkap}] (@{$owner->username})",
            'waktu_aktivitas'  => now(),
        ]);

        return redirect()->route('super-admin.owners.index')->with('success', 'Owner berhasil ditambahkan.');
    }

    public function edit(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $tenants = TempatParkir::orderBy('nama_tempat')->get(['id_tempat_parkir','nama_tempat']);
        return view('super_admin.owners.form', compact('tenants', 'owner'));
    }

    public function update(Request $request, User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'username' => 'required|string|max:50|alpha_dash|unique:user,username,'.$owner->id_user.',id_user',
            'password' => ['nullable', Password::min(8)],
            'id_tempat_parkir' => 'required|exists:tempat_parkir,id_tempat_parkir',
            'status_aktif' => 'nullable|boolean',
        ]);

        $payload = [
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'id_tempat_parkir' => $data['id_tempat_parkir'],
            'status_aktif' => $request->boolean('status_aktif') ? 1 : 0,
        ];
        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }
        $owner->update($payload);

        LogAktivitas::create([
            'id_tempat_parkir' => $owner->id_tempat_parkir,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Super Admin: Perbarui Akun Owner [{$owner->nama_lengkap}]",
            'waktu_aktivitas'  => now(),
        ]);

        return redirect()->route('super-admin.owners.index')->with('success', 'Owner berhasil diperbarui.');
    }

    public function toggle(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $owner->update(['status_aktif' => $owner->status_aktif ? 0 : 1]);

        LogAktivitas::create([
            'id_tempat_parkir' => $owner->id_tempat_parkir,
            'id_user'          => Auth::id(),
            'aktivitas'        => "Super Admin: Ubah Status Owner [{$owner->nama_lengkap}] menjadi " . ($owner->status_aktif ? 'Aktif' : 'Nonaktif'),
            'waktu_aktivitas'  => now(),
        ]);

        return back()->with('success', 'Status owner diperbarui.');
    }
}
