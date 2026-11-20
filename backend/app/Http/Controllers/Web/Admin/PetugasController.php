<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PetugasController extends Controller
{
    private function tenantId(): int { return (int) Auth::guard('web')->user()->id_tempat_parkir; }

    public function index(Request $request)
    {
        $petugasList = User::where('id_tempat_parkir', $this->tenantId())
            ->where('role','petugas')
            ->when($request->filled('q'), fn($q) => $q->where(fn($qq) => $qq->where('nama_lengkap','like','%'.$request->q.'%')->orWhere('username','like','%'.$request->q.'%')))
            ->withCount(['transaksiDilayani' => fn($q) => $q->where('id_tempat_parkir', $this->tenantId())])
            ->orderByDesc('id_user')
            ->paginate(10)->withQueryString();
        return view('admin.petugas.index', compact('petugasList'));
    }

    public function create() { return view('admin.petugas.form', ['petugas' => new User()]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'username' => 'required|string|max:50|alpha_dash|unique:user,username',
            'password' => ['required', Password::min(8)],
            'status_aktif' => 'nullable|boolean',
        ]);
        User::create([
            'nama_lengkap'=>$data['nama_lengkap'], 'username'=>$data['username'], 'password'=>Hash::make($data['password']),
            'role'=>'petugas', 'id_tempat_parkir'=>$this->tenantId(), 'status_aktif'=>$request->boolean('status_aktif',true)?1:0,
        ]);
        return redirect()->route('admin.petugas.index')->with('success','Petugas berhasil ditambahkan.');
    }

    public function edit(User $petugas)
    {
        abort_unless($petugas->role==='petugas' && $petugas->id_tempat_parkir===$this->tenantId(), 404);
        return view('admin.petugas.form', compact('petugas'));
    }

    public function update(Request $request, User $petugas)
    {
        abort_unless($petugas->role==='petugas' && $petugas->id_tempat_parkir===$this->tenantId(), 404);
        $data = $request->validate([
            'nama_lengkap'=>'required|string|max:50',
            'username'=>'required|string|max:50|alpha_dash|unique:user,username,'.$petugas->id_user.',id_user',
            'password'=>['nullable', Password::min(8)],
            'status_aktif'=>'nullable|boolean',
        ]);
        $payload = ['nama_lengkap'=>$data['nama_lengkap'],'username'=>$data['username'],'status_aktif'=>$request->boolean('status_aktif')?1:0];
        if (!empty($data['password'])) $payload['password']=Hash::make($data['password']);
        $petugas->update($payload);
        return redirect()->route('admin.petugas.index')->with('success','Petugas berhasil diperbarui.');
    }

    public function toggle(User $petugas)
    {
        abort_unless($petugas->role==='petugas' && $petugas->id_tempat_parkir===$this->tenantId(), 404);
        $petugas->update(['status_aktif'=>$petugas->status_aktif?0:1]);
        return back()->with('success','Status petugas diperbarui.');
    }
}
