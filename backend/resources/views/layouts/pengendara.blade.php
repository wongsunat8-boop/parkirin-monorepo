@extends('layouts.app')

@section('content')
@php
    $authUser = auth('pengendara')->user();
    $roleLabel = 'Pengendara';
    $homeRoute = route('pengendara.dashboard');
    $navItems = [
        ['label'=>'Dashboard', 'route'=>'pengendara.dashboard', 'active'=>'pengendara.dashboard', 'icon'=>'home'],
        ['label'=>'Scan Gerbang', 'route'=>'checkin.scanner', 'active'=>'checkin.scanner', 'icon'=>'scan'],
        ['label'=>'Riwayat', 'route'=>'pengendara.riwayat.index', 'active'=>'pengendara.riwayat.*', 'icon'=>'history'],
        ['label'=>'Katalog', 'route'=>'pengendara.katalog.index', 'active'=>'pengendara.katalog.*', 'icon'=>'search'],
        ['label'=>'Profil', 'route'=>'pengendara.profil.index', 'active'=>'pengendara.profil.*', 'icon'=>'user'],
    ];
    $claimCount = 0;
    $icon = function($name, $class='w-5 h-5') {
        $paths = [
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
            'scan' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.008v.008H6.75V6.75zM6.75 16.5h.008v.008H6.75V16.5zM16.5 6.75h.008v.008H16.5V6.75zM13.5 13.5h3.75m0 0v3.75m0-3.75l3.75 3.75M13.5 20.25h6.75"/>',
            'history' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>',
            'bell' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
            'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
            'collapse' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>',
            'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>',
        ];
        $p = $paths[$name] ?? $paths['home'];
        return '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">'.$p.'</svg>';
    };
@endphp

<style>
  :root { --sidebar-w: 240px; --sidebar-collapsed: 72px; }
  .shell { min-height: 100vh; background-color: #F9F9F9; }
  .bottom-nav { overflow-x: auto !important; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
  .bottom-nav::-webkit-scrollbar { display: none; }
  @media (max-width: 767.98px) {
    .app-sidebar { display: none !important; }
    .bottom-nav { display: flex !important; }
    .app-main { padding-left: 0 !important; padding-bottom: 76px !important; transition: none; }
  }
  @media (min-width: 768px) {
    .app-sidebar { display: flex !important; position: fixed; inset: 0 auto 0 0; width: var(--sidebar-w); background: #FFFFFF; border-right: 1px solid rgba(226, 232, 240, 0.8); z-index: 40; flex-direction: column; transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .shell.is-collapsed .app-sidebar { width: var(--sidebar-collapsed); }
    .bottom-nav { display: none !important; }
    .app-main { padding-left: var(--sidebar-w); padding-bottom: 0 !important; transition: padding-left 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .shell.is-collapsed .app-main { padding-left: var(--sidebar-collapsed); }
  }
  .app-topbar { position: sticky; top: 0; z-index: 30; height: 64px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 0 16px; }
  @media (min-width: 768px) { .app-topbar { padding: 0 24px; } }
  .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 12px; color: #475569; font: 500 13.5px/1.2 'Inter', sans-serif; text-decoration: none; white-space: nowrap; transition: all 0.15s ease; position: relative; }
  .nav-link:hover { background: #F1F5F9; color: #0F172A; }
  .nav-link.active { background: #0D63A5; color: #FFFFFF; font-weight: 600; box-shadow: 0 2px 6px rgba(13, 99, 165, 0.25); }
  .shell.is-collapsed .nav-link { justify-content: center; padding: 11px; }
  .shell.is-collapsed .nav-text, .shell.is-collapsed .nav-section-title, .shell.is-collapsed .user-meta-block { display: none !important; }
  .content-wrap { flex: 1; width: 100%; max-width: 1440px; margin: 0 auto; padding: 16px 16px 88px; }
  @media (min-width: 640px) { .content-wrap { padding: 20px 20px 88px; } }
  @media (min-width: 768px) { .content-wrap { padding: 24px 28px 36px; } }
  .bento-card { background: #FFFFFF; border-radius: 1rem; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04); padding: 1.25rem; transition: box-shadow 0.2s ease, border-color 0.2s ease; }
  .bento-card:hover { border-color: rgba(203, 213, 225, 0.9); }
  .ui-card { background:#fff; border:1px solid rgba(226, 232, 240, 0.8); border-radius:1rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
  .ui-card-h { padding:14px 18px; border-bottom:1px solid #f1f5f9; }
  .ui-card-t { font:700 15px/1.2 'Nunito', 'Inter', sans-serif; color:#212121; }
  .ui-card-d { font:400 12.5px/1.4 'Inter', sans-serif; color:#64748b; margin-top:2px; }
  .ui-card-b { padding:18px; }
  .ui-kpi { font:800 28px/1 'Nunito', sans-serif; letter-spacing:-.02em; color:#212121; }
  .ui-kpi-label { font:600 12px/1.2 'Inter', sans-serif; text-transform:uppercase; letter-spacing:.05em; color:#64748b; }
  .ui-kpi-sub { font:400 12.5px/1.3 'Inter', sans-serif; color:#64748b; margin-top:4px; }
  .btn-action-edit { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; line-height:1.2; background-color:#0D63A5; color:#FFFFFF !important; text-decoration:none; transition:all 0.15s ease; box-shadow:0 1px 2px rgba(13,99,165,0.2); border:none; cursor:pointer; }
  .btn-action-edit:hover { background-color:#0A4F84; color:#FFFFFF !important; }
  .btn-action-danger { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; line-height:1.2; background-color:#DC2626; color:#FFFFFF !important; text-decoration:none; border:none; cursor:pointer; transition:all 0.15s ease; box-shadow:0 1px 2px rgba(220,38,38,0.2); }
  .btn-action-danger:hover { background-color:#B91C1C; color:#FFFFFF !important; }
  .btn-action-success { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; line-height:1.2; background-color:#16A34A; color:#FFFFFF !important; text-decoration:none; border:none; cursor:pointer; transition:all 0.15s ease; box-shadow:0 1px 2px rgba(22,163,74,0.2); }
  .btn-action-success:hover { background-color:#15803D; color:#FFFFFF !important; }
  .btn-action-warning { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; line-height:1.2; background-color:#D97706; color:#FFFFFF !important; text-decoration:none; border:none; cursor:pointer; transition:all 0.15s ease; box-shadow:0 1px 2px rgba(217,119,6,0.2); }
  .btn-action-warning:hover { background-color:#B45309; color:#FFFFFF !important; }
  .btn-action-muted { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; line-height:1.2; background-color:#64748B; color:#FFFFFF !important; text-decoration:none; border:none; cursor:pointer; transition:all 0.15s ease; }
  .btn-action-muted:hover { background-color:#475569; color:#FFFFFF !important; }
  .ui-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:0.75rem; padding:8px 16px; font:600 13px/1.2 'Inter', sans-serif; text-decoration:none; border:1px solid transparent; cursor:pointer; transition:.15s; }
  .ui-btn-primary { background:#0D63A5; color:#fff; } .ui-btn-primary:hover { background:#0A4F84; }
  .ui-btn-secondary { background:#fff; color:#212121; border-color:#e2e8f0; } .ui-btn-secondary:hover { background:#f8fafc; border-color:#cbd5e1; }
  .ui-btn-ghost { background:transparent; color:#475569; } .ui-btn-ghost:hover { background:#f1f5f9; color:#0f172a; }
  .ui-btn-danger { background:#fff; color:#dc2626; border-color:#fecaca; } .ui-btn-danger:hover { background:#fef2f2; }
  .ui-btn-dark { background:#212121; color:#fff; } .ui-btn-dark:hover { background:#111827; }
  .ui-btn-sm { padding:6px 12px; font-size:12px; border-radius:0.625rem; }
  .ui-input, .ui-select { width:100%; border:1px solid #cbd5e1; border-radius:0.75rem; padding:9px 12px; font:400 13.5px/1.3 'Inter', sans-serif; color:#212121; background:#fff; transition: border-color .15s; }
  .ui-input:focus, .ui-select:focus { outline:none; border-color:#0D63A5; box-shadow:0 0 0 3px rgba(13,99,165,.12); }
  .ui-label { display:block; font:600 12.5px/1.2 'Inter', sans-serif; color:#334155; margin-bottom:5px; }
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
  .nav-badge { position:absolute; top:6px; right:10px; min-width:18px; height:18px; padding:0 5px; border-radius:9999px; background:#DC2626; color:#fff; font:700 10px/18px 'Inter',sans-serif; text-align:center; }
  .nav-badge-sm { top:2px; right:6px; min-width:16px; height:16px; font-size:9px; line-height:16px; }
  [x-cloak] { display: none !important; }
</style>

<div x-data="{ collapsed: (function(){ try { return localStorage.getItem('parkirin_sidebar_pengendara') === '1'; } catch(e){ return false; } })(), toggle(){ this.collapsed = !this.collapsed; try { localStorage.setItem('parkirin_sidebar_pengendara', this.collapsed ? '1':'0'); } catch(e){} } }" :class="{ 'is-collapsed': collapsed }" class="shell" id="appShell">
  <aside class="app-sidebar hidden md:flex" aria-label="Sidebar Navigasi Desktop">
    <div class="h-16 flex items-center justify-between border-b border-slate-100 shrink-0 px-4">
      <a href="{{ $homeRoute }}" class="flex items-center min-w-0" aria-label="Beranda Parkirin">
        <template x-if="!collapsed"><img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" class="h-7 w-auto object-contain"></template>
        <template x-if="collapsed"><div class="w-full flex justify-center"><img src="{{ asset('assets/logo/primary/Icon.svg') }}" alt="Parkirin" class="h-8 w-auto object-contain mx-auto"></div></template>
      </a>
    </div>
    <div class="flex-1 overflow-y-auto py-4 px-2 space-y-4">
      <div>
        <div class="nav-section-title px-3 pb-1.5 text-[11px] font-bold uppercase tracking-[.06em] text-slate-400">Menu Pengendara</div>
        <nav class="space-y-1">
          @foreach($navItems as $item)
            @php $active = request()->routeIs($item['active']); $isNotif = $item['icon']==='bell'; @endphp
            <a href="{{ route($item['route']) }}" class="nav-link {{ $active ? 'active' : '' }}" :title="collapsed ? '{{ $item['label'] }}' : ''">
              {!! $icon($item['icon'], 'w-5 h-5 shrink-0') !!}
              <span class="nav-text">{{ $item['label'] }}</span>
              @if($isNotif && $claimCount > 0)<span class="nav-badge" x-show="!collapsed">{{ $claimCount }}</span>@endif
            </a>
          @endforeach
        </nav>
      </div>
    </div>
    <div class="border-t border-slate-100 p-2.5 shrink-0 bg-slate-50/60">
      <template x-if="!collapsed">
        <div class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl bg-white border border-slate-200/70 shadow-2xs mb-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-heading font-extrabold text-xs shrink-0 ring-1 ring-primary/20">{{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U', 0, 1)) }}</span>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-[#212121] truncate">{{ $authUser?->nama_lengkap }}</div>
            <div class="text-[11px] text-slate-500 truncate">{{ $authUser?->email }}</div>
          </div>
        </div>
      </template>
      <template x-if="collapsed">
        <div class="flex justify-center mb-2" title="{{ $authUser?->nama_lengkap }}"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-heading font-extrabold text-xs ring-1 ring-primary/20">{{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U', 0, 1)) }}</span></div>
      </template>
      <div class="flex items-center gap-1.5" :class="collapsed ? 'flex-col' : 'justify-between px-1'">
        <button type="button" @click="toggle()" class="inline-flex items-center gap-1.5 py-1.5 px-2 rounded-lg text-slate-500 hover:text-primary hover:bg-slate-100 transition group text-left" :title="collapsed ? 'Lebarkan Sidebar (>>)' : 'Ciutkan Sidebar (<<)'">
          <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-primary nav-section-title">Account</span>
          <span class="text-xs font-extrabold text-slate-600 group-hover:text-primary flex items-center gap-1"><span x-text="collapsed ? '»' : '« Hide'"></span></span>
        </button>
        <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Keluar">{!! $icon('logout', 'w-4 h-4') !!}</button></form>
      </div>
    </div>
  </aside>

  <div class="app-main">
    <header class="app-topbar">
      <div class="flex items-center gap-3 min-w-0">
        <a href="{{ $homeRoute }}" class="md:hidden inline-flex items-center shrink-0 pr-1" aria-label="Beranda Parkirin"><img src="{{ asset('assets/logo/primary/Icon.svg') }}" alt="Parkirin" class="h-7 w-auto object-contain"></a>
        <div class="min-w-0">
          <div class="flex items-center gap-1.5 text-xs font-medium text-slate-400 truncate">
            <span class="font-semibold text-slate-500">Parkirin</span>
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-600 font-semibold truncate">Pengendara</span>
            <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            <span class="text-[#212121] font-bold truncate">@yield('page-title', 'Dashboard')</span>
          </div>
          @hasSection('page-desc')<div class="hidden sm:block text-[11.5px] text-slate-500 truncate mt-0.5">@yield('page-desc')</div>@endif
        </div>
      </div>
      <div class="flex items-center gap-2 sm:gap-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20"><span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span><span>{{ $roleLabel }}</span></span>
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
          <div class="w-8 h-8 rounded-full bg-primary text-white font-heading font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">{{ strtoupper(mb_substr($authUser?->nama_lengkap ?? 'U', 0, 1)) }}</div>
          <div class="hidden xl:block text-left">
            <div class="text-xs font-bold text-[#212121] leading-tight truncate max-w-[130px]">{{ $authUser?->nama_lengkap }}</div>
            <div class="text-[10.5px] text-slate-400 leading-tight truncate">{{ $authUser?->username ?? $authUser?->email }}</div>
          </div>
          <form method="POST" action="{{ route('logout') }}" class="md:hidden m-0">@csrf<button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Keluar">{!! $icon('logout', 'w-4 h-4') !!}</button></form>
        </div>
      </div>
    </header>

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
          <ul class="list-disc pl-5 space-y-0.5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
      @endif

      @yield('pengendara-content')
    </main>
  </div>

  <nav class="bottom-nav fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.07)] px-3 py-2 overflow-x-auto flex items-center justify-around gap-1" aria-label="Navigasi Utama Mobile">
    @foreach($navItems as $item)
      @php $active = request()->routeIs($item['active']); $isNotif = $item['icon']==='bell'; @endphp
      <a href="{{ route($item['route']) }}" class="shrink-0 min-w-[64px] px-2 py-1.5 flex flex-col items-center justify-center gap-1 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-150 relative {{ $active ? 'bnav-active text-primary bg-primary/10 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
        {!! $icon($item['icon'], 'w-5 h-5 shrink-0') !!}
        <span class="leading-tight text-[11px]">{{ $item['label'] }}</span>
        @if($isNotif && $claimCount > 0)<span class="nav-badge nav-badge-sm">{{ $claimCount }}</span>@endif
      </a>
    @endforeach
  </nav>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  (function(){
    try { if (localStorage.getItem('parkirin_sidebar_pengendara') === '1') { var el = document.getElementById('appShell'); if (el) el.classList.add('is-collapsed'); } } catch(e) {}
    var activeBnav = document.querySelector('.bottom-nav .bnav-active'); if (activeBnav) { activeBnav.scrollIntoView({ inline: 'center', block: 'nearest' }); }
    document.querySelectorAll('[data-qr]').forEach(function(el){ if (el.dataset.qrDone) return; el.dataset.qrDone='1'; try { new QRCode(el, { text: el.dataset.qr, width: 120, height: 120, colorDark:'#212121', colorLight:'#ffffff', correctLevel: QRCode.CorrectLevel.M }); } catch(e){} });
  })();
</script>
@endpush
