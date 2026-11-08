@extends('layouts.app')

@section('content')
@php
    $authUser = auth('web')->user();
    $role = $authUser?->role;
    $roleLabel = match($role) {
        'super_admin' => 'Super Admin', 'owner' => 'Owner', 'admin' => 'Admin', 'petugas' => 'Petugas', default => 'Staff',
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
            ['label'=>'Tenant', 'route'=>'super-admin.tenants.index', 'active'=>'super-admin.tenants.*', 'icon'=>'building'],
            ['label'=>'Owner', 'route'=>'super-admin.owners.index', 'active'=>'super-admin.owners.*', 'icon'=>'users'],
            ['label'=>'Log', 'route'=>'super-admin.log-aktivitas.index', 'active'=>'super-admin.log-aktivitas.*', 'icon'=>'log'],
        ],
        'owner' => [
            ['label'=>'Dashboard', 'route'=>'owner.dashboard', 'active'=>'owner.dashboard', 'icon'=>'home'],
            ['label'=>'Admin', 'route'=>'owner.admins.index', 'active'=>'owner.admins.*', 'icon'=>'users'],
            ['label'=>'Banned', 'route'=>'owner.pengajuan-banned.index', 'active'=>'owner.pengajuan-banned.*', 'icon'=>'shield'],
            ['label'=>'Transaksi', 'route'=>'owner.transaksi.index', 'active'=>'owner.transaksi.*', 'icon'=>'receipt'],
        ],
        'admin' => [
            ['label'=>'Dashboard', 'route'=>'admin.dashboard', 'active'=>'admin.dashboard', 'icon'=>'home'],
            ['label'=>'Tarif', 'route'=>'admin.tarif.index', 'active'=>'admin.tarif.*', 'icon'=>'tag'],
            ['label'=>'Kapasitas', 'route'=>'admin.kapasitas.index', 'active'=>'admin.kapasitas.*', 'icon'=>'gauge'],
            ['label'=>'Petugas', 'route'=>'admin.petugas.index', 'active'=>'admin.petugas.*', 'icon'=>'users'],
            ['label'=>'Kendaraan', 'route'=>'admin.kendaraan.index', 'active'=>'admin.kendaraan.*', 'icon'=>'car'],
            ['label'=>'Pengendara', 'route'=>'admin.pengendara.index', 'active'=>'admin.pengendara.*', 'icon'=>'id'],
            ['label'=>'Log', 'route'=>'admin.log-aktivitas.index', 'active'=>'admin.log-aktivitas.*', 'icon'=>'log'],
        ],
        'petugas' => [
            ['label'=>'Dashboard', 'route'=>'petugas.dashboard', 'active'=>'petugas.dashboard', 'icon'=>'home'],
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
  :root { --sidebar-w: 248px; --sidebar-collapsed: 72px; }
  .shell { min-height: 100vh; background: #f6f7f9; }
  .app-sidebar { position:fixed; inset:0 auto 0 0; width:var(--sidebar-w); background:#fff; border-right:1px solid #e7e9ee; z-index:40; display:flex; flex-direction:column; transition: width .2s ease, transform .2s ease; }
  .shell.is-collapsed .app-sidebar { width: var(--sidebar-collapsed); }
  .app-main { padding-left:0; min-height:100vh; display:flex; flex-direction:column; }
  @media (min-width:1024px){ .app-main { padding-left: var(--sidebar-w); transition: padding-left .2s ease; } .shell.is-collapsed .app-main { padding-left: var(--sidebar-collapsed); } }
  .app-topbar { position:sticky; top:0; z-index:30; height:56px; background:rgba(255,255,255,.9); backdrop-filter: blur(8px); border-bottom:1px solid #e7e9ee; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:0 16px; }
  @media (min-width:1024px){ .app-topbar { padding:0 24px; } }
  .nav-link { display:flex; align-items:center; gap:12px; padding:9px 12px; border-radius:10px; color:#374151; font:500 13.5px/1.2 Inter,sans-serif; text-decoration:none; white-space:nowrap; }
  .nav-link:hover { background:#f3f4f6; color:#111827; }
  .nav-link.active { background:#0D63A5; color:#fff; box-shadow: 0 1px 2px rgba(13,99,165,.25); }
  .nav-link .nav-text { overflow:hidden; text-overflow:ellipsis; }
  .shell.is-collapsed .nav-link { justify-content:center; padding:10px; }
  .shell.is-collapsed .nav-text, .shell.is-collapsed .nav-section, .shell.is-collapsed .brand-text, .shell.is-collapsed .user-meta { display:none; }
  .bottom-nav { position:fixed; left:0; right:0; bottom:0; z-index:40; background:rgba(255,255,255,.97); backdrop-filter: blur(8px); border-top:1px solid #e7e9ee; display:flex; align-items:stretch; overflow-x:auto; -webkit-overflow-scrolling:touch; padding:6px 2px calc(6px + env(safe-area-inset-bottom)); scrollbar-width:none; }
  .bottom-nav::-webkit-scrollbar { display:none; }
  @media (min-width:1024px){ .bottom-nav { display:none; } }
  .bnav-item { flex:1 0 64px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; padding:4px 2px; color:#6b7280; text-decoration:none; font:500 10.5px/1.1 Inter,sans-serif; min-width:64px; border-radius:10px; }
  .bnav-item.active { color:#0D63A5; background:#eff6ff; }
  .bnav-item span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:100%; text-align:center; }
  .content-wrap { flex:1; width:100%; max-width: 1440px; margin:0 auto; padding: 16px 16px 88px; }
  @media (min-width:640px){ .content-wrap { padding: 20px 20px 88px; } }
  @media (min-width:1024px){ .content-wrap { padding: 24px 28px 32px; } }
  /* shadcn-like primitives */
  .ui-card { background:#fff; border:1px solid #e7e9ee; border-radius:14px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
  .ui-card-h { padding:14px 16px; border-bottom:1px solid #eef0f4; }
  .ui-card-t { font:700 14.5px/1.2 Inter,sans-serif; color:#111827; }
  .ui-card-d { font:400 12.5px/1.4 Inter,sans-serif; color:#6b7280; margin-top:2px; }
  .ui-card-b { padding:16px; }
  .ui-kpi { font:800 26px/1 Nunito,Inter,sans-serif; letter-spacing:-.02em; color:#111827; }
  .ui-kpi-label { font:600 11.5px/1.2 Inter,sans-serif; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
  .ui-kpi-sub { font:400 12px/1.3 Inter,sans-serif; color:#6b7280; margin-top:4px; }
  .ui-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:10px; padding:8px 14px; font:600 13px/1.2 Inter,sans-serif; text-decoration:none; border:1px solid transparent; cursor:pointer; transition:.15s; }
  .ui-btn-primary { background:#0D63A5; color:#fff; } .ui-btn-primary:hover { background:#0A4F84; }
  .ui-btn-secondary { background:#fff; color:#111827; border-color:#d9dde5; } .ui-btn-secondary:hover { background:#f8f9fb; }
  .ui-btn-ghost { background:transparent; color:#374151; } .ui-btn-ghost:hover { background:#f3f4f6; }
  .ui-btn-danger { background:#fff; color:#dc2626; border-color:#fecaca; } .ui-btn-danger:hover { background:#fef2f2; }
  .ui-btn-dark { background:#111827; color:#fff; } .ui-btn-dark:hover { background:#1f2937; }
  .ui-btn-sm { padding:6px 10px; font-size:12.5px; border-radius:8px; }
  .ui-link { color:#0D63A5; font-weight:600; text-decoration:none; } .ui-link:hover { text-decoration:underline; }
  .ui-input, .ui-select { width:100%; border:1px solid #d9dde5; border-radius:10px; padding:8px 11px; font:400 13.5px/1.3 Inter,sans-serif; color:#111827; background:#fff; }
  .ui-input:focus, .ui-select:focus { outline:none; border-color:#0D63A5; box-shadow:0 0 0 3px rgba(13,99,165,.12); }
  .ui-label { display:block; font:600 12.5px/1.2 Inter,sans-serif; color:#374151; margin-bottom:5px; }
  .ui-badge { display:inline-flex; align-items:center; border-radius:999px; padding:2px 8px; font:600 11px/1.4 Inter,sans-serif; }
  .ui-badge-success { background:#dcfce7; color:#166534; } .ui-badge-warning { background:#fef3c7; color:#92400e; }
  .ui-badge-danger { background:#fee2e2; color:#991b1b; } .ui-badge-muted { background:#f3f4f6; color:#4b5563; } .ui-badge-info { background:#e0f2fe; color:#075985; }
  .ui-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
  .ui-table { width:100%; border-collapse:separate; border-spacing:0; font:400 13px/1.4 Inter,sans-serif; }
  .ui-table th { text-align:left; padding:10px 14px; font:600 11.5px/1.2 Inter,sans-serif; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; background:#fafbfc; border-bottom:1px solid #eef0f4; white-space:nowrap; }
  .ui-table td { padding:11px 14px; border-bottom:1px solid #f1f3f6; color:#111827; vertical-align:middle; }
  .ui-table tbody tr:hover { background:#fafbfc; }
  .ui-table tbody tr:last-child td { border-bottom:0; }
  .ui-alert { border-radius:12px; padding:11px 14px; font:400 13px/1.45 Inter,sans-serif; border:1px solid; }
  .ui-alert-success { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
  .ui-alert-danger { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
  .ui-alert-warning { background:#fffbeb; color:#92400e; border-color:#fde68a; }
  .page-h { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:16px; }
  .page-title { font:800 22px/1.15 Nunito,Inter,sans-serif; color:#111827; margin:0; letter-spacing:-.01em; }
  .page-desc { font:400 13.5px/1.45 Inter,sans-serif; color:#6b7280; margin-top:2px; }
</style>

<div class="shell" id="appShell">
  {{-- Desktop sidebar (edge, collapsible) --}}
  <aside class="app-sidebar hidden lg:flex">
    <div class="h-14 flex items-center justify-between gap-2 px-3 border-b border-gray-100 shrink-0">
      <a href="{{ $homeRoute }}" class="flex items-center gap-2 min-w-0 px-1">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white font-heading font-extrabold text-sm shrink-0">P</span>
        <span class="brand-text min-w-0">
          <span class="block font-heading font-extrabold text-[15px] text-gray-900 leading-tight">Parkirin</span>
          <span class="block text-[10.5px] text-gray-500 leading-tight">{{ $roleLabel }}</span>
        </span>
      </a>
      <button type="button" id="sidebarToggle" class="ui-btn-ghost rounded-lg p-1.5 text-gray-500 shrink-0" title="Ciutkan/luaskan sidebar" aria-label="Toggle sidebar">
        <span class="toggle-icon">{!! $icon('collapse','w-4 h-4') !!}</span>
      </button>
    </div>
    <div class="flex-1 overflow-y-auto py-3 px-2">
      <div class="nav-section px-3 pb-1 text-[10.5px] font-semibold uppercase tracking-[.06em] text-gray-400">Navigasi</div>
      <nav class="space-y-0.5">
        @foreach($navItems as $item)
          @php $active = request()->routeIs($item['active']); @endphp
          <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'active' : '' }}" title="{{ $item['label'] }}">
            {!! $icon($item['icon'],'w-[18px] h-[18px] shrink-0') !!}
            <span class="nav-text">{{ $item['label'] }}</span>
          </a>
        @endforeach
      </nav>
      @hasSection('sidebar-extra')
        <div class="nav-section px-3 pt-4 pb-1 text-[10.5px] font-semibold uppercase tracking-[.06em] text-gray-400">Lainnya</div>
        <div class="nav-text text-sm space-y-0.5">@yield('sidebar-extra')</div>
      @endif
    </div>
    <div class="border-t border-gray-100 p-2 shrink-0">
      <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-semibold text-xs shrink-0">{{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U',0,1)) }}</span>
        <div class="user-meta min-w-0 flex-1">
          <div class="text-[13px] font-semibold text-gray-900 truncate">{{ $authUser?->nama_lengkap }}</div>
          <div class="text-[11px] text-gray-500 truncate">{{ $authUser?->tempatParkir->nama_tempat ?? $roleLabel }}</div>
        </div>
      </div>
      <form method="POST" action="{{ route('logout') }}" class="mt-1">
        @csrf
        <button type="submit" class="nav-link w-full text-red-600 hover:bg-red-50 hover:text-red-700" title="Keluar">
          {!! $icon('logout','w-[18px] h-[18px] shrink-0') !!}<span class="nav-text">Keluar</span>
        </button>
      </form>
    </div>
  </aside>

  <div class="app-main">
    {{-- Top bar --}}
    <header class="app-topbar">
      <div class="flex items-center gap-3 min-w-0">
        <a href="{{ $homeRoute }}" class="lg:hidden inline-flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white font-heading font-extrabold text-sm">P</span>
          <span class="font-heading font-extrabold text-gray-900">Parkirin</span>
        </a>
        <div class="hidden sm:block min-w-0">
          <div class="text-[13.5px] font-semibold text-gray-900 truncate">@yield('page-title', $roleLabel)</div>
          @hasSection('page-desc')<div class="text-[11.5px] text-gray-500 truncate">@yield('page-desc')</div>@endif
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="ui-badge ui-badge-info hidden md:inline-flex">{{ $roleLabel }}</span>
        @if($authUser?->tempatParkir)
          <span class="hidden lg:inline text-[12px] text-gray-500 max-w-[220px] truncate">{{ $authUser->tempatParkir->nama_tempat }}</span>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="lg:hidden m-0">
          @csrf
          <button type="submit" class="ui-btn ui-btn-ghost p-2 text-red-600" aria-label="Keluar">{!! $icon('logout','w-5 h-5') !!}</button>
        </form>
      </div>
    </header>

    <main class="content-wrap">
      @if(session('success'))
        <div class="ui-alert ui-alert-success mb-4">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="ui-alert ui-alert-danger mb-4">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="ui-alert ui-alert-danger mb-4">
          <ul class="list-disc pl-5 space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
      @endif
      @yield('staff-content')
    </main>
  </div>

  {{-- Mobile bottom nav --}}
  <nav class="bottom-nav" aria-label="Navigasi utama">
    @foreach($navItems as $item)
      @php $active = request()->routeIs($item['active']); @endphp
      <a href="{{ route($item['route']) }}" class="bnav-item {{ $active ? 'active' : '' }}">
        {!! $icon($item['icon'],'w-[22px] h-[22px]') !!}
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function(){
  const shell = document.getElementById('appShell');
  const btn = document.getElementById('sidebarToggle');
  if(!shell||!btn) return;
  const key = 'parkirin.sidebar.collapsed';
  try { if(localStorage.getItem(key)==='1') shell.classList.add('is-collapsed'); } catch(e){}
  btn.addEventListener('click', function(){
    shell.classList.toggle('is-collapsed');
    try { localStorage.setItem(key, shell.classList.contains('is-collapsed') ? '1':'0'); } catch(e){}
  });
})();
</script>
@endpush
