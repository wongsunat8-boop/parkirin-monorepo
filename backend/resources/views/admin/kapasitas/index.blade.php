@extends('layouts.staff')
@section('title', 'Kelola Kapasitas & Layanan')
@section('page-title', 'Kelola Kapasitas & Kuota')
@section('page-desc', 'Atur kuota slot parkir reguler, kuota menginap, serta aktivasi layanan online tenant.')

@section('staff-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Kapasitas & Kuota Lahan</h1>
            <p class="text-xs text-slate-500 mt-1">Tenant: <strong class="text-slate-800">{{ $tenant->nama_tempat }}</strong></p>
        </div>
    </div>

    {{-- Bento Overview Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kapasitas Reguler</span>
            <div class="text-3xl font-heading font-black text-primary mt-1">
                {{ number_format($tenant->kapasitas_reguler) }}
                <span class="text-sm font-normal text-slate-400">slot</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Slot maksimal kendaraan harian</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kuota Inap</span>
            <div class="text-3xl font-heading font-black text-purple-600 mt-1">
                {{ number_format($tenant->kuota_inap) }}
                <span class="text-sm font-normal text-slate-400">slot</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Alokasi khusus booking inap</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Keterisian Saat Ini</span>
            <div class="text-3xl font-heading font-extrabold text-amber-600 mt-1">
                {{ number_format($tenant->terisi) }}
                <span class="text-sm font-normal text-slate-400">unit</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Sisa slot: {{ max(0, $tenant->kapasitas_reguler - $tenant->terisi) }} unit</p>
        </div>
    </div>

    {{-- Main Settings Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Card: Edit Kapasitas & Kuota --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-heading font-extrabold text-base text-[#212121]">Pengaturan Slot Kapasitas</h3>
                <p class="text-xs text-slate-500 mt-0.5">Semua perubahan kapasitas diamankan dengan konkurensi row-level lock.</p>
            </div>

            <form method="POST" action="{{ route('admin.kapasitas.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="ui-label">Kapasitas Reguler (Slot)</label>
                    <input type="number" min="1" name="kapasitas" value="{{ old('kapasitas', $tenant->kapasitas_reguler) }}" required class="ui-input font-bold text-slate-800">
                    <p class="text-[11px] text-slate-400 mt-1">Batas maksimum kendaraan reguler yang dapat check-in secara bersamaan.</p>
                </div>

                <div>
                    <label class="ui-label">Kuota Parkir Inap (Slot)</label>
                    <input type="number" min="0" name="kuota_inap" value="{{ old('kuota_inap', $tenant->kuota_inap) }}" required class="ui-input font-bold text-slate-800">
                    <p class="text-[11px] text-slate-400 mt-1">Jumlah slot yang disediakan untuk layanan booking inap terjadwal.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="ui-btn ui-btn-primary text-xs">
                        Simpan Perubahan Kapasitas
                    </button>
                </div>
            </form>
        </div>

        {{-- Card: Toggle Layanan Tenant --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 space-y-4 flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-heading font-extrabold text-base text-[#212121]">Aktivasi Layanan Online</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kendali fitur yang dibuka untuk pengendara di platform Parkirin.</p>
                </div>

                <div class="space-y-4">
                    {{-- Toggle Booking Inap --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-bold text-sm text-slate-900">Layanan Booking Inap</div>
                            <p class="text-xs text-slate-500 mt-0.5">Izinkan pengendara mereservasi slot inap melalui katalog web/PWA.</p>
                            <span class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold {{ $tenant->sediakan_booking_inap ? 'text-emerald-700' : 'text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $tenant->sediakan_booking_inap ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                Status: {{ $tenant->sediakan_booking_inap ? 'Aktif Melayani Booking' : 'Layanan Dinonaktifkan' }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.kapasitas.toggle-booking') }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="{{ $tenant->sediakan_booking_inap ? 'btn-action-warning' : 'btn-action-success' }}">
                                {{ $tenant->sediakan_booking_inap ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>

                    {{-- Toggle Member Pass --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-bold text-sm text-slate-900">Layanan Member Langganan</div>
                            <p class="text-xs text-slate-500 mt-0.5">Buka penjualan paket membership bulanan bagi pengendara rutin.</p>
                            <span class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold {{ $tenant->sediakan_member ? 'text-emerald-700' : 'text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $tenant->sediakan_member ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                Status: {{ $tenant->sediakan_member ? 'Aktif Menjual Member' : 'Layanan Dinonaktifkan' }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.kapasitas.toggle-member') }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="{{ $tenant->sediakan_member ? 'btn-action-warning' : 'btn-action-success' }}">
                                {{ $tenant->sediakan_member ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200/60 text-[11px] text-blue-900 leading-relaxed">
                <strong>Catatan:</strong> Palang gerbang masuk otomatis tidak mengeluarkan karcis baru jika tempat parkir reguler sudah terisi penuh.
            </div>
        </div>
    </div>

    {{-- Card: QRIS Pembayaran Tenant --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6 space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="font-heading font-extrabold text-base text-[#212121]">QRIS Pembayaran Resmi Tenant</h3>
                <p class="text-xs text-slate-500 mt-0.5">Gambar QRIS ini akan ditampilkan kepada pengendara saat melakukan pembayaran online Member Pass dan Booking Inap.</p>
            </div>
            @if($tenant->qris_image)
                <span class="ui-badge ui-badge-success text-[10.5px]">QRIS Aktif</span>
            @else
                <span class="ui-badge ui-badge-warning text-[10.5px]">Belum Diunggah (Menggunakan QRIS Default)</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-[200px,1fr] gap-6 items-center">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-center">
                @if($tenant->qris_image)
                    <img src="{{ asset($tenant->qris_image) }}" alt="QRIS {{ $tenant->nama_tempat }}" class="w-40 h-40 object-contain mx-auto rounded-lg border border-slate-200 bg-white p-1 shadow-2xs">
                    <span class="text-[10px] text-emerald-700 font-bold block mt-1.5">QRIS Kustom Terpasang</span>
                @else
                    <div class="w-40 h-40 mx-auto rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center p-2 text-slate-400">
                        <svg class="w-10 h-10 mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span class="text-[10px] text-center font-semibold">Belum Ada File QRIS</span>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.kapasitas.qris') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="ui-label">Unggah Gambar QRIS Statis / Barcode Rekening</label>
                    <input type="file" name="qris_image" accept="image/*" required class="ui-input text-xs">
                    <p class="text-[11px] text-slate-400 mt-1">Mendukung file gambar PNG, JPG, JPEG, WebP (Maks 4MB). Pastikan QRIS jelas dan dapat dipindai oleh aplikasi m-Banking / e-Wallet.</p>
                </div>

                <div class="pt-1">
                    <button type="submit" class="ui-btn ui-btn-primary text-xs">
                        {{ $tenant->qris_image ? 'Perbarui Gambar QRIS' : 'Unggah & Aktifkan QRIS' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
