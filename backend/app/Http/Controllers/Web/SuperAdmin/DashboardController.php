<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TempatParkir;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenant' => TempatParkir::count(),
            'tenant_aktif' => TempatParkir::where('status_aktif', 1)->count(),
            'total_owner' => User::where('role', 'owner')->count(),
            'owner_aktif' => User::where('role', 'owner')->where('status_aktif', 1)->count(),
        ];

        $recentTenants = TempatParkir::withCount(['users'])
            ->orderByDesc('id_tempat_parkir')
            ->limit(6)
            ->get();

        $chart = [
            'tenant' => ['Aktif' => (int)$stats['tenant_aktif'], 'Nonaktif' => max(0, (int)$stats['total_tenant'] - (int)$stats['tenant_aktif'])],
            'owner' => ['Aktif' => (int)$stats['owner_aktif'], 'Nonaktif' => max(0, (int)$stats['total_owner'] - (int)$stats['owner_aktif'])],
        ];

        return view('super_admin.dashboard', compact('stats', 'recentTenants', 'chart'));
    }
}
