@extends('layouts.app')

@section('content')
@php
    $authUser = auth('web')->user();
    $role = $authUser?->role;
    $roleLabel = match($role) {
        'super_admin' => 'Super Admin',
        'owner' => 'Owner',
        'admin' => 'Admin',
        'petugas' => 'Petugas',
        default => 'Staff',
    };
    $homeRoute = match($role) {
        'super_admin' => route('super-admin.dashboard'),
        'owner' => route('owner.dashboard'),
        'admin' => route('admin.dashboard'),
        'petugas' => route('petugas.dashboard'),
        default => '/',
    };
    $navItems = match($role) {
        'super_admin' => [
            ['label'=>'Dashboard', 'route'=>'super-admin.dashboard', 'active'=>'super-admin.dashboard', 'icon'=>'home'],
            ['label'=>'Lokasi Parkir', 'route'=>'super-admin.tenants.index', 'active'=>'super-admin.tenants.*', 'icon'=>'building'],
            ['label'=>'Owner', 'route'=>'super-admin.owners.index', 'active'=>'super-admin.owners.*', 'icon'=>'users'],
            ['label'=>'Log', 'route'=>'super-admin.log-aktivitas.index', 'active'=>'super-admin.log-aktivitas.*', 'icon'=>'log'],
        ],
        'owner' => [
            ['label'=>'Dashboard', 'route'=>'owner.dashboard', 'active'=>'owner.dashboard', 'icon'=>'home'],
            ['label'=>'Admin', 'route'=>'owner.admins.index', 'active'=>'owner.admins.*', 'icon'=>'users'],
            ['label'=>'Transaksi', 'route'=>'owner.transaksi.index', 'active'=>'owner.transaksi.*', 'icon'=>'receipt'],
            ['label'=>'Cek Setoran Kasir', 'route'=>'owner.audit-setoran.index', 'active'=>'owner.audit-setoran.*', 'icon'=>'receipt'],
        ],
        'admin' => [
            ['label'=>'Dashboard', 'route'=>'admin.dashboard', 'active'=>'admin.dashboard', 'icon'=>'home'],
            ['label'=>'Verifikasi Bayar', 'route'=>'admin.verifikasi.index', 'active'=>'admin.verifikasi.*', 'icon'=>'shield'],
            ['label'=>'Paket Member', 'route'=>'admin.paket-langganan.index', 'active'=>'admin.paket-langganan.*', 'icon'=>'tag'],
            ['label'=>'Tarif', 'route'=>'admin.tarif.index', 'active'=>'admin.tarif.*', 'icon'=>'tag'],
            ['label'=>'Kapasitas', 'route'=>'admin.kapasitas.index', 'active'=>'admin.kapasitas.*', 'icon'=>'gauge'],
            ['label'=>'QR Gerbang', 'route'=>'admin.qr-gate.index', 'active'=>'admin.qr-gate.*', 'icon'=>'qr'],
            ['label'=>'Petugas', 'route'=>'admin.petugas.index', 'active'=>'admin.petugas.*', 'icon'=>'users'],
            ['label'=>'Pengendara', 'route'=>'admin.pengendara.index', 'active'=>'admin.pengendara.*', 'icon'=>'id'],
            ['label'=>'Log', 'route'=>'admin.log-aktivitas.index', 'active'=>'admin.log-aktivitas.*', 'icon'=>'log'],
        ],
        'petugas' => [
            ['label'=>'Dashboard', 'route'=>'petugas.dashboard', 'active'=>'petugas.dashboard', 'icon'=>'home'],
            ['label'=>'Scan Keluar', 'route'=>'petugas.scanner-keluar', 'active'=>'petugas.scanner-keluar*', 'icon'=>'qr'],
        ],
        default => [],
    };
    $icon = function($name, $class='w-5 h-5') {
        $paths = [
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
            'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286z"/>',
            'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/>',
            'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>',
            'qr' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h3v3h-3v-3zM13.5 19.5h3v.75h-3v-.75zM19.5 13.5h.75v3h-.75v-3zM19.5 19.5h.75v.75h-.75v-.75z"/>',
            'gauge' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>',
            'car' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
            'id' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>',
            'log' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
            'collapse' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>',
            'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>',
        ];
        $p = $paths[$name] ?? $paths['home'];
        return '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">'.$p.'</svg>';
    };
@endphp

<style>
  :root {
    --sidebar-w: 240px;
    --sidebar-collapsed: 72px;
  }
  .shell {
    min-height: 100vh;
    background-color: #F9F9F9;
  }

  /* ═══════════════════════════════════════════
     RESPONSIVE NAVIGATION BREAKPOINT (< 768px vs >= 768px)
     Aturan Ketat: Jika Bottom Nav muncul, Sidebar WAJIB hilang total (display: none !important).
     Jika Sidebar muncul, Bottom Nav WAJIB hilang total (display: none !important).
  ═══════════════════════════════════════════ */
  /* Bottom Navigation Bar styling (Horizontal scrollable for Admin with preserved item sizes) */
  .bottom-nav {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; /* Firefox */
  }
  .bottom-nav::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
  }

  @media (max-width: 767.98px) {
    .app-sidebar {
      display: none !important;
    }
    .bottom-nav {
      display: flex !important;
    }
    .app-main {
      padding-left: 0 !important;
      padding-bottom: 76px !important;
      transition: none;
    }
  }

  @media (min-width: 768px) {
    .app-sidebar {
      display: flex !important;
      position: fixed;
      inset: 0 auto 0 0;
      width: var(--sidebar-w);
      background: #FFFFFF;
      border-right: 1px solid rgba(226, 232, 240, 0.8);
      z-index: 40;
      flex-direction: column;
      transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .shell.is-collapsed .app-sidebar {
      width: var(--sidebar-collapsed);
    }
    .bottom-nav {
      display: none !important;
    }
    .app-main {
      padding-left: var(--sidebar-w);
      padding-bottom: 0 !important;
      transition: padding-left 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .shell.is-collapsed .app-main {
      padding-left: var(--sidebar-collapsed);
    }
  }

  .app-topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    height: 64px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 0 16px;
  }
  @media (min-width: 768px) {
    .app-topbar {
      padding: 0 24px;
    }
  }

  /* Nav Links */
  .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 12px;
    color: #475569;
    font: 500 13.5px/1.2 'Inter', sans-serif;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s ease;
  }
  .nav-link:hover {
    background: #F1F5F9;
    color: #0F172A;
  }
  .nav-link.active {
    background: #0D63A5;
    color: #FFFFFF;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(13, 99, 165, 0.25);
  }
  .shell.is-collapsed .nav-link {
    justify-content: center;
    padding: 11px;
  }
  .shell.is-collapsed .nav-text,
  .shell.is-collapsed .nav-section-title,
  .shell.is-collapsed .user-meta-block {
    display: none !important;
  }

  /* Content wrap */
  .content-wrap {
    flex: 1;
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 16px 88px;
  }
  @media (min-width: 640px) {
    .content-wrap {
      padding: 20px 20px 88px;
    }
  }
  @media (min-width: 768px) {
    .content-wrap {
      padding: 24px 28px 36px;
    }
  }

  /* Bento Grid Card Primitive */
  .bento-card {
    background: #FFFFFF;
    border-radius: 1rem;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
    padding: 1.25rem;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .bento-card:hover {
    border-color: rgba(203, 213, 225, 0.9);
  }

  /* Preserved UI helpers for child views */
  .ui-card { background:#fff; border:1px solid rgba(226, 232, 240, 0.8); border-radius:1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
  .ui-card-h { padding:14px 18px; border-bottom:1px solid #f1f5f9; }
  .ui-card-t { font:700 15px/1.2 'Nunito', 'Inter', sans-serif; color:#212121; }
  .ui-card-d { font:400 12.5px/1.4 'Inter', sans-serif; color:#64748b; margin-top:2px; }
  .ui-card-b { padding:18px; }
  .ui-kpi { font:800 28px/1 'Nunito', sans-serif; letter-spacing:-.02em; color:#212121; }
  .ui-kpi-label { font:600 12px/1.2 'Inter', sans-serif; text-transform:uppercase; letter-spacing:.05em; color:#64748b; }
  .ui-kpi-sub { font:400 12.5px/1.3 'Inter', sans-serif; color:#64748b; margin-top:4px; }

  /* Standard Table Action Button Highlights */
  .btn-action-edit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    background-color: #0D63A5;
    color: #FFFFFF !important;
    text-decoration: none;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(13, 99, 165, 0.2);
  }
  .btn-action-edit:hover {
    background-color: #0A4F84;
    color: #FFFFFF !important;
  }

  .btn-action-danger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    background-color: #DC2626;
    color: #FFFFFF !important;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(220, 38, 38, 0.2);
  }
  .btn-action-danger:hover {
    background-color: #B91C1C;
    color: #FFFFFF !important;
  }

  .btn-action-success {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    background-color: #16A34A;
    color: #FFFFFF !important;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(22, 163, 74, 0.2);
  }
  .btn-action-success:hover {
    background-color: #15803D;
    color: #FFFFFF !important;
  }

  .btn-action-warning {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    background-color: #D97706;
    color: #FFFFFF !important;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(217, 119, 6, 0.2);
  }
  .btn-action-warning:hover {
    background-color: #B45309;
    color: #FFFFFF !important;
  }

  .btn-action-muted {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    background-color: #64748B;
    color: #FFFFFF !important;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .btn-action-muted:hover {
    background-color: #475569;
    color: #FFFFFF !important;
  }

  /* Action Buttons General */
  .ui-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:0.75rem; padding:8px 16px; font:600 13px/1.2 'Inter', sans-serif; text-decoration:none; border:1px solid transparent; cursor:pointer; transition:.15s; }
  .ui-btn-primary { background:#0D63A5; color:#fff; } .ui-btn-primary:hover { background:#0A4F84; }
  .ui-btn-secondary { background:#fff; color:#212121; border-color:#e2e8f0; } .ui-btn-secondary:hover { background:#f8fafc; border-color:#cbd5e1; }
  .ui-btn-ghost { background:transparent; color:#475569; } .ui-btn-ghost:hover { background:#f1f5f9; color:#0f172a; }
  .ui-btn-danger { background:#fff; color:#dc2626; border-color:#fecaca; } .ui-btn-danger:hover { background:#fef2f2; }
  .ui-btn-dark { background:#212121; color:#fff; } .ui-btn-dark:hover { background:#111827; }
  .ui-btn-sm { padding:6px 12px; font-size:12px; border-radius:0.625rem; }

  /* Forms & Tables */
  .ui-input, .ui-select { width:100%; border:1px solid #cbd5e1; border-radius:0.75rem; padding:9px 12px; font:400 13.5px/1.3 'Inter', sans-serif; color:#212121; background:#fff; transition: border-color .15s; }
  .ui-input:focus, .ui-select:focus { outline:none; border-color:#0D63A5; box-shadow:0 0 0 3px rgba(13,99,165,.12); }
  .ui-label { display:block; font:600 12.5px/1.2 'Inter', sans-serif; color:#334155; margin-bottom:5px; }

  /* Semantic status pill badges */
  .ui-badge { display:inline-flex; align-items:center; border-radius:9999px; padding:3px 10px; font:600 11.5px/1.3 'Inter', sans-serif; }
  .ui-badge-success { background:#dcfce7; color:#15803d; border:1px solid rgba(187, 247, 208, 0.8); }
  .ui-badge-warning { background:#fef3c7; color:#b45309; border:1px solid rgba(253, 230, 138, 0.8); }
  .ui-badge-danger { background:#fee2e2; color:#b91c1c; border:1px solid rgba(254, 202, 202, 0.8); }
  .ui-badge-muted { background:#f1f5f9; color:#475569; border:1px solid rgba(226, 232, 240, 0.8); }
  .ui-badge-info { background:#e0f2fe; color:#0369a1; border:1px solid rgba(186, 230, 253, 0.8); }

  .ui-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
  .ui-table { width:100%; border-collapse:separate; border-spacing:0; font:400 13px/1.4 'Inter', sans-serif; }
  .ui-table th { text-align:left; padding:12px 16px; font:600 11.5px/1.2 'Inter', sans-serif; text-transform:uppercase; letter-spacing:.05em; color:#64748b; background:#f8fafc; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
  .ui-table td { padding:13px 16px; border-bottom:1px solid #f1f5f9; color:#212121; vertical-align:middle; }
  .ui-table tbody tr:hover { background:#f8fafc; }
  .ui-table tbody tr:last-child td { border-bottom:0; }

  .ui-alert { border-radius:0.875rem; padding:12px 16px; font:400 13px/1.45 'Inter', sans-serif; border:1px solid; }
  .ui-alert-success { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
  .ui-alert-danger { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
  .ui-alert-warning { background:#fffbeb; color:#92400e; border-color:#fde68a; }

  .page-h { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:20px; }
  .page-title { font:800 24px/1.15 'Nunito', sans-serif; color:#212121; margin:0; letter-spacing:-.02em; }
  .page-desc { font:400 13.5px/1.45 'Inter', sans-serif; color:#64748b; margin-top:2px; }
</style>

<div x-data="{
        collapsed: (function() {
            try { return localStorage.getItem('parkirin_sidebar_collapsed') === '1'; } catch(e) { return false; }
        })(),
        toggle() {
            this.collapsed = !this.collapsed;
            try { localStorage.setItem('parkirin_sidebar_collapsed', this.collapsed ? '1' : '0'); } catch(e) {}
        }
    }"
    :class="{ 'is-collapsed': collapsed }"
    class="shell"
    id="appShell">

  {{-- ═══════════════════════════════════════════
      DESKTOP COLLAPSIBLE SIDEBAR (hidden on mobile, visible md+)
      Layar Desktop (md: ke atas): Tampilkan HANYA Collapsible Sidebar (hidden md:flex)
      Layar Mobile (< md): Sembunyikan Sidebar sepenuhnya (hidden)
  ═══════════════════════════════════════════ --}}
  <aside class="app-sidebar hidden md:flex" aria-label="Sidebar Navigasi Desktop">
    {{-- Header Logo Area --}}
    <div class="h-16 flex items-center justify-between border-b border-slate-100 shrink-0 px-4">
      <a href="{{ $homeRoute }}" class="flex items-center min-w-0" aria-label="Beranda Parkirin">
        {{-- Expanded: Tampilkan HANYA Teks.svg (Logo lengkap) --}}
        <template x-if="!collapsed">
          <img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" class="h-7 w-auto object-contain">
        </template>
        {{-- Collapsed: Tampilkan HANYA Icon.svg (Ikon tunggal) --}}
        <template x-if="collapsed">
          <div class="w-full flex justify-center">
            <img src="{{ asset('assets/logo/primary/Icon.svg') }}" alt="Parkirin" class="h-8 w-auto object-contain mx-auto">
          </div>
        </template>
      </a>
    </div>

    {{-- Middle Navigation Area --}}
    <div class="flex-1 overflow-y-auto py-4 px-2 space-y-4">
      <div>
        <div class="nav-section-title px-3 pb-1.5 text-[11px] font-bold uppercase tracking-[.06em] text-slate-400">
          Menu Utama
        </div>
        <nav class="space-y-1">
          @foreach($navItems as $item)
            @php $active = request()->routeIs($item['active']); @endphp
            <a href="{{ route($item['route']) }}"
               class="nav-link {{ $active ? 'active' : '' }}"
               :title="collapsed ? '{{ $item['label'] }}' : ''">
              {!! $icon($item['icon'], 'w-5 h-5 shrink-0') !!}
              <span class="nav-text">{{ $item['label'] }}</span>
            </a>
          @endforeach
        </nav>
      </div>

      @hasSection('sidebar-extra')
        <div>
          <div class="nav-section-title px-3 pt-2 pb-1.5 text-[11px] font-bold uppercase tracking-[.06em] text-slate-400">
            Aksi Tambahan
          </div>
          <div class="nav-text space-y-1">
            @yield('sidebar-extra')
          </div>
        </div>
      @endif
    </div>

    {{-- Bottom Account & Toggle Section (Reference: dashboard-with-collapsible-sidebar) --}}
    <div class="border-t border-slate-100 p-2.5 shrink-0 bg-slate-50/60">
      {{-- User Profile Snippet --}}
      <template x-if="!collapsed">
        <div class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl bg-white border border-slate-200/70 shadow-2xs mb-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-heading font-extrabold text-xs shrink-0 ring-1 ring-primary/20">
            {{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U', 0, 1)) }}
          </span>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-[#212121] truncate">{{ $authUser?->nama_lengkap }}</div>
            <div class="text-[11px] text-slate-500 truncate">{{ $authUser?->tempatParkir->nama_tempat ?? $roleLabel }}</div>
          </div>
        </div>
      </template>

      <template x-if="collapsed">
        <div class="flex justify-center mb-2" title="{{ $authUser?->nama_lengkap }} ({{ $roleLabel }})">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-heading font-extrabold text-xs ring-1 ring-primary/20">
            {{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U', 0, 1)) }}
          </span>
        </div>
      </template>

      {{-- Collapsible Sidebar Button `<< Hide` / `>>` --}}
      <div class="flex items-center gap-1.5" :class="collapsed ? 'flex-col' : 'justify-between px-1'">
        <button type="button"
                @click="toggle()"
                class="inline-flex items-center gap-1.5 py-1.5 px-2 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100 transition group text-left"
                :title="collapsed ? 'Lebarkan Sidebar (>>)' : 'Ciutkan Sidebar (<<)'">
          <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-primary nav-section-title">Account</span>
          <span class="text-xs font-extrabold text-slate-600 group-hover:text-primary flex items-center gap-1">
            <span x-text="collapsed ? '»' : '« Hide'"></span>
          </span>
        </button>

        {{-- Logout button --}}
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit"
                  class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                  title="Keluar">
            {!! $icon('logout', 'w-4 h-4') !!}
          </button>
        </form>
      </div>
    </div>
  </aside>

  {{-- ═══════════════════════════════════════════
      MAIN VIEW AREA
  ═══════════════════════════════════════════ --}}
  <div class="app-main">
    {{-- Modern Top Header: Breadcrumb & Role + Profile Indicator --}}
    <header class="app-topbar">
      <div class="flex items-center gap-3 min-w-0">
        {{-- Mobile Logo Header: Tampilkan HANYA Icon.svg (Ikon tunggal) --}}
        <a href="{{ $homeRoute }}" class="md:hidden inline-flex items-center shrink-0 pr-1" aria-label="Beranda Parkirin">
          <img src="{{ asset('assets/logo/primary/Icon.svg') }}" alt="Parkirin" class="h-7 w-auto object-contain">
        </a>

        {{-- Breadcrumb Navigation (Left side) --}}
        <div class="min-w-0">
          <div class="flex items-center gap-1.5 text-xs font-medium text-slate-400 truncate">
            <span class="hover:text-primary transition font-semibold text-slate-500">Parkirin</span>
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 font-semibold truncate">{{ $roleLabel }}</span>
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            <span class="text-[#212121] font-bold truncate">@yield('page-title', 'Overview')</span>
          </div>
          @hasSection('page-desc')
            <div class="hidden sm:block text-[11.5px] text-slate-500 truncate mt-0.5">@yield('page-desc')</div>
          @endif
        </div>
      </div>

      {{-- Role Indicator & Profile (Right side) --}}
      <div class="flex items-center gap-2 sm:gap-3">
        @if($authUser?->tempatParkir)
          <span class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
            <svg class="w-3.5 h-3.5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span class="max-w-[170px] truncate">{{ $authUser->tempatParkir->nama_tempat }}</span>
          </span>
        @endif

        {{-- Role Pill Badge --}}
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
          <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
          <span>{{ $roleLabel }}</span>
        </span>

        {{-- User Profile Pill & Quick Logout for Mobile --}}
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
          <div class="w-8 h-8 rounded-full bg-primary text-white font-heading font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
            {{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U', 0, 1)) }}
          </div>
          <div class="hidden xl:block text-left">
            <div class="text-xs font-bold text-[#212121] leading-tight truncate max-w-[130px]">{{ $authUser?->nama_lengkap }}</div>
            <div class="text-[10.5px] text-slate-400 leading-tight truncate">{{ $authUser?->username ?? $roleLabel }}</div>
          </div>
          <form method="POST" action="{{ route('logout') }}" class="md:hidden m-0">
            @csrf
            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Keluar">
              {!! $icon('logout', 'w-4 h-4') !!}
            </button>
          </form>
        </div>
      </div>
    </header>

    {{-- Main Content Canvas --}}
    <main class="content-wrap">
      @if(session('success'))
        <div class="ui-alert ui-alert-success mb-5 flex items-center gap-2 shadow-2xs">
          <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif
      @if(session('error'))
        <div class="ui-alert ui-alert-danger mb-5 flex items-center gap-2 shadow-2xs">
          <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif
      @if(isset($errors) && $errors->any())
        <div class="ui-alert ui-alert-danger mb-5 shadow-2xs">
          <div class="font-semibold text-xs mb-1">Periksa kembali data input:</div>
          <ul class="list-disc pl-5 space-y-0.5 text-xs">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('staff-content')
    </main>
  </div>

  {{-- ═══════════════════════════════════════════
      MOBILE BOTTOM NAVIGATION BAR
      Layar Mobile (< md): Tampilkan HANYA Bottom Navigation Bar
      Layar Desktop (md: ke atas): Sembunyikan Bottom Navigation Bar (md:hidden)
      Khusus Admin / menu banyak: Scroll horizontal tanpa mengecilkan/menimpa menu
  ═══════════════════════════════════════════ --}}
  <nav class="bottom-nav fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.07)] px-3 py-2 overflow-x-auto flex items-center {{ count($navItems) > 4 ? 'justify-start' : 'justify-around' }} gap-2"
       aria-label="Navigasi Utama Mobile">
    @foreach($navItems as $item)
      @php $active = request()->routeIs($item['active']); @endphp
      <a href="{{ route($item['route']) }}"
         class="shrink-0 min-w-[70px] px-2.5 py-1.5 flex flex-col items-center justify-center gap-1 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-150 {{ $active ? 'bnav-active text-primary bg-primary/10 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
        {!! $icon($item['icon'], 'w-5 h-5 shrink-0') !!}
        <span class="leading-tight">{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>
</div>
@endsection

@push('scripts')
<script>
  // Fallback initial sync if Alpine loads slightly after DOM
  (function(){
    try {
      if (localStorage.getItem('parkirin_sidebar_collapsed') === '1') {
        var el = document.getElementById('appShell');
        if (el) el.classList.add('is-collapsed');
      }
    } catch(e) {}

    // Auto-scroll item aktif ke tengah pandangan pada mobile bottom nav
    var activeBnav = document.querySelector('.bottom-nav .bnav-active');
    if (activeBnav) {
      activeBnav.scrollIntoView({ inline: 'center', block: 'nearest' });
    }
  })();
</script>
@endpush
