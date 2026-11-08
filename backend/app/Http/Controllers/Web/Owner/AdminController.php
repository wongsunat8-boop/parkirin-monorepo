<?php

namespace App\Http\Controllers\Web\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    private function tenantId(): int
    {
        return (int) Auth::guard('web')->user()->id_tempat_parkir;
    }

    public function index(Request $request)
    {
        $admins = User::where('role', 'admin')
            ->where('id_tempat_parkir', $this->tenantId())
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(fn($qq) => $qq->where('nama_lengkap', 'like', '%'.$request->q.'%')
                    ->orWhere('username', 'like', '%'.$request->q.'%'));
            })
            ->orderByDesc('id_user')
            ->paginate(15)
            ->withQueryString();

        return view('owner.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('owner.admins.form', ['admin' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'username' => 'required|string|max:50|alpha_dash|unique:user,username',
            'password' => ['required', Password::min(8)],
            'status_aktif' => 'nullable|boolean',
        ]);
        User::create([
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'id_tempat_parkir' => $this->tenantId(),
            'status_aktif' => $request->boolean('status_aktif', true) ? 1 : 0,
        ]);
        return redirect()->route('owner.admins.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(User $admin)
    {
        abort_unless($admin->role === 'admin' && $admin->id_tempat_parkir === $this->tenantId(), 404);
        return view('owner.admins.form', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        abort_unless($admin->role === 'admin' && $admin->id_tempat_parkir === $this->tenantId(), 404);
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'username' => 'required|string|max:50|alpha_dash|unique:user,username,'.$admin->id_user.',id_user',
            'password' => ['nullable', Password::min(8)],
            'status_aktif' => 'nullable|boolean',
        ]);
        $payload = [
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'status_aktif' => $request->boolean('status_aktif') ? 1 : 0,
        ];
        if (!empty($data['password'])) $payload['password'] = Hash::make($data['password']);
        $admin->update($payload);
        return redirect()->route('owner.admins.index')->with('success', 'Admin berhasil diperbarui.');
    }

    public function toggle(User $admin)
    {
        abort_unless($admin->role === 'admin' && $admin->id_tempat_parkir === $this->tenantId(), 404);
        $admin->update(['status_aktif' => $admin->status_aktif ? 0 : 1]);
        return back()->with('success', 'Status admin diperbarui.');
    }
}
