@extends('layouts.staff')
@section('title', 'Cek Setoran Kasir per Shift')
@section('page-title', 'Cek Setoran Kasir')
@section('page-desc', 'Rekapitulasi penerimaan uang tunai dan non-tunai per petugas jaga untuk pencocokan fisik uang kasir.')

@section('staff-content')
<div class="space-y-6">

    {{-- Top Action & Print Bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Rekap Setoran Kasir</h1>
            <p class="text-xs text-slate-500 mt-1">
                Cocokkan jumlah fisik <strong>Uang Tunai</strong> yang diserahkan petugas di akhir jam kerja dengan catatan sistem.
            </p>
        </div>
        <a href="{{ route('owner.audit-setoran.print', ['dari' => $dari, 'sampai' => $sampai, 'id_petugas' => $petugasId]) }}" target="_blank" class="ui-btn ui-btn-secondary text-xs shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Rekap Setoran</span>
        </a>
    </div>

    {{-- Bento KPI Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Wajib Setor Tunai</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-heading font-black text-emerald-600">
                Rp {{ number_format($summary['total_tunai'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Uang kasir fisik wajib disetor</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-primary">Penerimaan Non-Tunai</span>
                <span class="w-2 h-2 rounded-full bg-primary"></span>
            </div>
            <div class="text-2xl font-heading font-black text-primary">
                Rp {{ number_format($summary['total_non_tunai'], 0, ',', '.') }}
            </div>
            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10.5px] bg-slate-100 text-slate-700 font-semibold border border-slate-200/60" title="QRIS di Pos Kasir Keluar">
                    Pos: Rp {{ number_format($summary['non_tunai_kasir'] ?? 0, 0, ',', '.') }}
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10.5px] bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/60" title="Member Pass Lunas">
                    Member: Rp {{ number_format($summary['non_tunai_member'] ?? 0, 0, ',', '.') }}
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10.5px] bg-purple-50 text-purple-700 font-semibold border border-purple-200/60" title="Booking Inap Lunas">
                    Booking: Rp {{ number_format($summary['non_tunai_booking'] ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Keseluruhan Pemasukan</span>
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            </div>
            <div class="text-2xl font-heading font-black text-[#212121]">
                Rp {{ number_format($summary['grand_total'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Seluruh metode pembayaran</p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-wrap gap-3 items-end">
        <div class="w-36">
            <label class="ui-label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}" class="ui-input text-xs">
        </div>
        <div class="w-36">
            <label class="ui-label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="ui-input text-xs">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="ui-label">Petugas Kasir</label>
            <select name="id_petugas" class="ui-select text-xs">
                <option value="">Semua Petugas</option>
                @foreach($daftarPetugas as $pet)
                    <option value="{{ $pet->id_user }}" @selected($petugasId == $pet->id_user)>{{ $pet->nama_lengkap }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="ui-btn ui-btn-dark ui-btn-sm">Filter</button>
            <a href="{{ route('owner.audit-setoran.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm">Reset</a>
        </div>
    </form>

    {{-- Shift Audit Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Tanggal Shift</th>
                        <th class="py-3 px-4">Petugas Kasir Keluar</th>
                        <th class="py-3 px-4 text-center">Volume Transaksi</th>
                        <th class="py-3 px-4 text-right">Setoran Tunai (Wajib Kasir)</th>
                        <th class="py-3 px-4 text-right">Non-Tunai (QRIS / Pass)</th>
                        <th class="py-3 px-4 text-right">Total Penerimaan</th>
                        <th class="py-3 px-4 text-center">Status Setoran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($shifts as $s)
                    @php
                        $kasir = $petugasMap->get($s->id_petugas_keluar);
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ \Carbon\Carbon::parse($s->tanggal_shift)->translatedFormat('d F Y') }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($kasir)
                                <div class="font-bold text-slate-900">{{ $kasir->nama_lengkap }}</div>
                                <div class="text-[10.5px] text-slate-400 font-mono">&#64;{{ $kasir->username }}</div>
                            @else
                                <span class="text-slate-400 italic">Self Check-Out / Sistem</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                {{ number_format($s->total_transaksi) }} karcis
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-extrabold text-emerald-600 text-sm">
                            Rp {{ number_format($s->setoran_tunai, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-semibold text-primary">
                            Rp {{ number_format($s->non_tunai, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-[#212121]">
                            Rp {{ number_format($s->total_omzet, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Siap Dicocokkan
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400">
                            Tidak ada data transaksi shift kasir pada rentang tanggal ini.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>

    {{-- Online Non-Cash Audit Table (Member Pass & Booking Inap Terverifikasi) --}}
    @php
        $onlineTrx = collect();
        if (isset($memberList)) {
            foreach ($memberList as $m) {
                $onlineTrx->push([
                    'tipe' => 'Member Pass',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'kode' => $m->kode_member,
                    'waktu' => $m->waktu_unggah_bukti ?? $m->waktu_registrasi ?? $m->tanggal_mulai,
                    'pengendara' => $m->pengendara?->nama_lengkap ?? 'Rider',
                    'kontak' => $m->pengendara?->no_telepon ?? '-',
                    'plat' => $m->kendaraan?->plat_nomor ?? '-',
                    'jenis' => $m->kendaraan?->jenis_kendaraan ?? '-',
                    'rincian' => $m->paket?->nama_paket ?? 'Paket Langganan',
                    'nominal' => (float) $m->biaya_langganan,
                    'verifikator' => $m->userVerifikasi?->nama_lengkap ?? 'Admin / Otomatis',
                    'status' => $m->status_pembayaran,
                ]);
            }
        }
        if (isset($bookingList)) {
            foreach ($bookingList as $b) {
                $onlineTrx->push([
                    'tipe' => 'Booking Inap',
                    'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'kode' => $b->kode_booking,
                    'waktu' => $b->waktu_unggah_bukti ?? $b->waktu_booking,
                    'pengendara' => $b->pengendara?->nama_lengkap ?? 'Rider',
                    'kontak' => $b->pengendara?->no_telepon ?? '-',
                    'plat' => $b->kendaraan?->plat_nomor ?? '-',
                    'jenis' => $b->kendaraan?->jenis_kendaraan ?? '-',
                    'rincian' => 'Inap ' . \Carbon\Carbon::parse($b->waktu_mulai_inap)->format('d/m') . ' s/d ' . \Carbon\Carbon::parse($b->waktu_rencana_keluar)->format('d/m/Y'),
                    'nominal' => (float) $b->biaya_inap_dibayar,
                    'verifikator' => $b->userVerifikasi?->nama_lengkap ?? 'Admin / Otomatis',
                    'status' => $b->status_pembayaran,
                ]);
            }
        }
        $onlineTrx = $onlineTrx->sortByDesc('waktu');
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden mt-6">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    Rekap Penerimaan Non-Tunai Online (Member Pass & Booking Inap Terverifikasi)
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Transaksi pembayaran online QRIS yang diverifikasi oleh Admin dan langsung masuk ke saldo rekening/QRIS mitra tempat parkir.
                </p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
                Total: Rp {{ number_format(($summary['non_tunai_member'] ?? 0) + ($summary['non_tunai_booking'] ?? 0), 0, ',', '.') }}
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Layanan</th>
                        <th class="py-3 px-4">Kode Transaksi</th>
                        <th class="py-3 px-4">Pengendara</th>
                        <th class="py-3 px-4">Kendaraan</th>
                        <th class="py-3 px-4">Paket / Jadwal</th>
                        <th class="py-3 px-4 text-right">Nominal Lunas</th>
                        <th class="py-3 px-4">Diverifikasi Oleh</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($onlineTrx as $item)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-medium text-slate-700 whitespace-nowrap">
                            {{ $item['waktu'] ? \Carbon\Carbon::parse($item['waktu'])->translatedFormat('d M Y, H:i') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $item['badge_class'] }}">
                                {{ $item['tipe'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                            {{ $item['kode'] }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $item['pengendara'] }}</div>
                            <div class="text-[10.5px] text-slate-400 font-mono">{{ $item['kontak'] }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-mono font-bold text-slate-900">{{ $item['plat'] }}</div>
                            <div class="text-[10.5px] text-slate-400">{{ $item['jenis'] }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700">
                            {{ $item['rincian'] }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-primary text-sm whitespace-nowrap">
                            Rp {{ number_format($item['nominal'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $item['verifikator'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $item['status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">
                            Tidak ada data transaksi non-tunai online (member / booking inap) pada rentang tanggal ini.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
