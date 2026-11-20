@extends('layouts.staff')
@section('title', 'Verifikasi Pembayaran Online')
@section('page-title', 'Verifikasi Pembayaran Online')
@section('page-desc', 'Periksa dan setujui bukti transfer QRIS untuk paket Member dan Booking Inap.')

@section('staff-content')
<div class="space-y-6" x-data="{
    proofModal: false,
    proofImg: '',
    proofTitle: '',
    proofTime: '',
    proofAmount: '',
    openProof(url, title, time, amount) {
        this.proofImg = url;
        this.proofTitle = title;
        this.proofTime = time;
        this.proofAmount = amount;
        this.proofModal = true;
    }
}">

    {{-- Alert Toast Banner --}}
    @if($totalUnverified > 0)
    <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="font-heading font-extrabold text-sm sm:text-base text-amber-950 flex items-center gap-2">
                    <span>Perhatian: Ada {{ $totalUnverified }} Pembayaran yang Belum Dicek!</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900">{{ $totalUnverified }} Baru</span>
                </div>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    Pengendara dapat langsung masuk sementara bukti pembayaran menunggu dicek. Segera cocokkan <strong>Waktu Unggah Bukti</strong> dengan uang masuk di mutasi QRIS/bank. Jika bukti tidak sesuai atau palsu, segera klik <strong>Tolak</strong> agar akses otomatis dibatalkan.
                </p>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            <a href="{{ route('admin.verifikasi.index', ['tab' => 'unverified']) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                Lihat Yang Belum Dicek
            </a>
        </div>
    </div>
    @else
    <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-4 shadow-2xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="text-xs text-emerald-900">
            <strong class="font-bold text-emerald-950">Semua Pembayaran Beres!</strong> Tidak ada bukti transfer online yang tertunda atau belum diverifikasi.
        </div>
    </div>
    @endif

    {{-- Bento KPI Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Belum Dicek</span>
                <div class="text-2xl font-heading font-black text-amber-600 mt-0.5">{{ number_format($totalUnverified) }}</div>
                <p class="text-[11px] text-slate-500 mt-0.5">Perlu dicocokkan dengan mutasi uang</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Member Belum Dicek</span>
                <div class="text-2xl font-heading font-black text-primary mt-0.5">{{ number_format($unverifiedMemberCount) }}</div>
                <p class="text-[11px] text-slate-500 mt-0.5">Paket langganan bulanan</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Booking Inap Belum Dicek</span>
                <div class="text-2xl font-heading font-black text-purple-600 mt-0.5">{{ number_format($unverifiedBookingCount) }}</div>
                <p class="text-[11px] text-slate-500 mt-0.5">Slot menginap terjadwal</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    {{-- Filter & Tab Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Tab Navigasi --}}
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl">
            <a href="{{ route('admin.verifikasi.index', ['tab' => 'unverified', 'q' => request('q')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $tab === 'unverified' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                <span>Belum Dicek</span>
                @if($totalUnverified > 0)
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                @endif
            </a>
            <a href="{{ route('admin.verifikasi.index', ['tab' => 'member', 'q' => request('q')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'member' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                Semua Member Pass
            </a>
            <a href="{{ route('admin.verifikasi.index', ['tab' => 'booking', 'q' => request('q')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $tab === 'booking' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                Semua Booking Inap
            </a>
        </div>

        {{-- Search Input --}}
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative flex-1 sm:w-72">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode, pengendara, plat..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary focus:outline-none">
            </div>
            <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black transition">Cari</button>
        </form>
    </div>

    {{-- SECTION 1: LANGGANAN MEMBER TABLE --}}
    @if($tab === 'unverified' || $tab === 'member')
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-extrabold text-sm text-[#212121]">Verifikasi Paket Langganan Member</h3>
                <p class="text-xs text-slate-400">Bukti pembayaran langganan member via QRIS</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-primary border border-blue-100">
                Member Pass
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Kode / Pengendara</th>
                        <th class="py-3 px-4">Paket & Kendaraan</th>
                        <th class="py-3 px-4">Biaya</th>
                        <th class="py-3 px-4">Waktu Unggah Struk</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($members as $m)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $m->kode_member }}</div>
                            <div class="text-[11px] text-slate-500">{{ $m->pengendara->nama_lengkap ?? 'Anonim' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $m->pengendara->no_telepon ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-primary">{{ $m->paket->nama_paket ?? 'Paket Member' }}</div>
                            <div class="mt-0.5 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white font-mono text-[10px] font-bold">{{ $m->kendaraan->plat_nomor ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400">({{ $m->kendaraan->jenis_kendaraan ?? '-' }})</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#212121]">
                            Rp {{ number_format($m->biaya_langganan, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-mono text-slate-800">{{ optional(\Carbon\Carbon::parse($m->waktu_unggah_bukti))->format('d M Y H:i:s') }}</div>
                            <div class="text-[10.5px] text-slate-400">{{ optional(\Carbon\Carbon::parse($m->waktu_unggah_bukti))->diffForHumans() }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(is_null($m->id_user_verifikasi))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Belum Dicek
                                </span>
                            @elseif($m->status_pembayaran === 'Lunas')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Lunas (Disetujui)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Tombol Cek Struk --}}
                                @if($m->bukti_bayar)
                                <button type="button"
                                        @click="openProof('{{ asset($m->bukti_bayar) }}', 'Member: {{ $m->kode_member }} ({{ $m->pengendara->nama_lengkap ?? '' }})', '{{ optional(\Carbon\Carbon::parse($m->waktu_unggah_bukti))->format('d M Y H:i:s') }}', 'Rp {{ number_format($m->biaya_langganan, 0, ',', '.') }}')"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Bukti</span>
                                </button>
                                @endif

                                @if(is_null($m->id_user_verifikasi))
                                    {{-- Tombol Setujui Verifikasi --}}
                                    <form method="POST" action="{{ route('admin.verifikasi.member.verifikasi', $m) }}" class="inline" onsubmit="return confirm('Setujui pembayaran Member Pass ini sebagai Lunas?')">
                                        @csrf
                                        <button type="submit" class="btn-action-success" title="Setujui Pembayaran">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Setujui</span>
                                        </button>
                                    </form>

                                    {{-- Tombol Tolak --}}
                                    <form method="POST" action="{{ route('admin.verifikasi.member.tolak', $m) }}" class="inline" onsubmit="return confirm('Tolak pembayaran ini? Status langganan akan dibatalkan.')">
                                        @csrf
                                        <button type="submit" class="btn-action-danger" title="Tolak Bukti">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Tolak</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Dicek oleh: {{ $m->userVerifikasi->nama_lengkap ?? 'Staff' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Tidak ada data paket member yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $members->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- SECTION 2: BOOKING INAP TABLE --}}
    @if($tab === 'unverified' || $tab === 'booking')
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-extrabold text-sm text-[#212121]">Verifikasi Pembayaran Booking Inap</h3>
                <p class="text-xs text-slate-400">Bukti pembayaran reservasi parkir inap via QRIS</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                Booking Inap
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Kode Booking</th>
                        <th class="py-3 px-4">Pengendara & Plat</th>
                        <th class="py-3 px-4">Periode Inap</th>
                        <th class="py-3 px-4">Biaya Inap</th>
                        <th class="py-3 px-4">Waktu Unggah Struk</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($bookings as $b)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $b->kode_booking }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ optional(\Carbon\Carbon::parse($b->waktu_booking))->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $b->pengendara->nama_lengkap ?? 'Anonim' }}</div>
                            <div class="mt-0.5 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white font-mono text-[10px] font-bold">{{ $b->kendaraan->plat_nomor ?? '-' }}</span>
                                <span class="text-[11px] text-slate-400">({{ $b->kendaraan->jenis_kendaraan ?? '-' }})</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            <div>{{ optional(\Carbon\Carbon::parse($b->waktu_mulai_inap))->format('d M H:i') }}</div>
                            <div class="text-[11px] text-slate-400">s/d {{ optional(\Carbon\Carbon::parse($b->waktu_rencana_keluar))->format('d M H:i') }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#212121]">
                            Rp {{ number_format($b->biaya_inap_dibayar, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-mono text-slate-800">{{ optional(\Carbon\Carbon::parse($b->waktu_unggah_bukti))->format('d M Y H:i:s') }}</div>
                            <div class="text-[10.5px] text-slate-400">{{ optional(\Carbon\Carbon::parse($b->waktu_unggah_bukti))->diffForHumans() }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(is_null($b->id_user_verifikasi))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Belum Dicek
                                </span>
                            @elseif($b->status_pembayaran === 'Lunas')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Lunas (Disetujui)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($b->bukti_bayar)
                                <button type="button"
                                        @click="openProof('{{ asset($b->bukti_bayar) }}', 'Booking: {{ $b->kode_booking }}', '{{ optional(\Carbon\Carbon::parse($b->waktu_unggah_bukti))->format('d M Y H:i:s') }}', 'Rp {{ number_format($b->biaya_inap_dibayar, 0, ',', '.') }}')"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Bukti</span>
                                </button>
                                @endif

                                @if(is_null($b->id_user_verifikasi))
                                    <form method="POST" action="{{ route('admin.verifikasi.booking.verifikasi', $b) }}" class="inline" onsubmit="return confirm('Setujui pembayaran booking ini sebagai Lunas?')">
                                        @csrf
                                        <button type="submit" class="btn-action-success" title="Setujui Pembayaran">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Setujui</span>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.verifikasi.booking.tolak', $b) }}" class="inline" onsubmit="return confirm('Tolak pembayaran booking ini? Status booking akan dibatalkan.')">
                                        @csrf
                                        <button type="submit" class="btn-action-danger" title="Tolak Bukti">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Tolak</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Dicek oleh: {{ $b->userVerifikasi->nama_lengkap ?? 'Staff' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Tidak ada data booking yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- MODAL PREVIEW BUKTI BAYAR --}}
    <div x-show="proofModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @keydown.escape.window="proofModal = false">
        <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200"
             @click.away="proofModal = false">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="font-heading font-extrabold text-sm text-[#212121]" x-text="proofTitle"></h4>
                    <p class="text-xs text-slate-400">Lihat Foto Bukti Pembayaran QRIS</p>
                </div>
                <button type="button" @click="proofModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 bg-slate-50 flex items-center justify-center min-h-[250px] max-h-[60vh] overflow-auto">
                <img :src="proofImg" alt="Bukti Transfer" class="max-h-[55vh] object-contain rounded-xl shadow-xs border border-slate-200">
            </div>
            <div class="p-4 border-t border-slate-100 bg-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                <div>
                    <div class="text-slate-500">Nominal: <strong class="text-slate-900 font-extrabold" x-text="proofAmount"></strong></div>
                    <div class="text-slate-400 text-[11px]">Waktu Unggah: <span class="font-mono text-slate-700" x-text="proofTime"></span></div>
                </div>
                <button type="button" @click="proofModal = false" class="ui-btn ui-btn-secondary text-xs">
                    Tutup Gambar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
