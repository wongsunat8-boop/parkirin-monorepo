<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
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
            ->paginate(15)
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
        User::create([
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'owner',
            'id_tempat_parkir' => $data['id_tempat_parkir'],
            'status_aktif' => $request->boolean('status_aktif', true) ? 1 : 0,
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
        if (!empty($data['password'])) $payload['password'] = Hash::make($data['password']);
        $owner->update($payload);

        return redirect()->route('super-admin.owners.index')->with('success', 'Owner berhasil diperbarui.');
    }

    public function toggle(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $owner->update(['status_aktif' => $owner->status_aktif ? 0 : 1]);
        return back()->with('success', 'Status owner diperbarui.');
    }
}
