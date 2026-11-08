<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $logs = LogAktivitas::with([
                'user:id_user,nama_lengkap,role',
                'tempatParkir:id_tempat_parkir,nama_tempat',
            ])
            ->whereHas('user', fn($q) => $q->where('role', 'super_admin'))
            ->orderByDesc('id_log')
            ->cursorPaginate(20)
            ->withQueryString();

        return view('super_admin.log_aktivitas.index', compact('logs'));
    }
}
