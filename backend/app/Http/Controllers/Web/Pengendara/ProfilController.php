<?php

namespace App\Http\Controllers\Web\Pengendara;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        return view('pengendara.profil.index');
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'email'        => ['required', 'email', 'max:100', Rule::unique('pengendara', 'email')->ignore($pengendara->id_pengendara, 'id_pengendara')],
            'username'     => ['nullable', 'string', 'max:50', Rule::unique('pengendara', 'username')->ignore($pengendara->id_pengendara, 'id_pengendara')],
            'no_telepon'   => ['nullable', 'string', 'max:20'],
        ]);

        $pengendara->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $pengendara = auth('pengendara')->user();
        
        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!$pengendara->password || !Hash::check($validated['password_lama'], $pengendara->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
        }

        $pengendara->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
