@extends('layouts.staff')
@section('title', ($paket->exists ? 'Edit' : 'Tambah').' Paket Langganan')
@section('page-title', ($paket->exists ? 'Edit' : 'Tambah').' Paket Langganan')
@section('page-desc', 'Konfigurasi parameter paket, tarif, durasi, dan batas masuk harian.')

@section('staff-content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-heading font-extrabold text-base text-[#212121]">
                    {{ $paket->exists ? 'Perbarui Paket Langganan' : 'Buat Paket Langganan Baru' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Isi seluruh informasi paket dengan teliti.</p>
            </div>
            <a href="{{ route('admin.paket-langganan.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ $paket->exists ? route('admin.paket-langganan.update', $paket) : route('admin.paket-langganan.store') }}" class="p-6 space-y-5">
            @csrf
            @if($paket->exists)
                @method('PUT')
            @endif

            {{-- Nama Paket --}}
            <div>
                <label class="ui-label">Nama Paket <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_paket" value="{{ old('nama_paket', $paket->nama_paket) }}" required placeholder="Contoh: Member Motor Bulanan Hemat, VIP All-Day" class="ui-input">
                <p class="text-[11px] text-slate-400 mt-1">Nama paket yang akan dilihat oleh pengendara di katalog.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Jenis Kendaraan --}}
                <div>
                    <label class="ui-label">Jenis Kendaraan <span class="text-rose-500">*</span></label>
                    <select name="jenis_kendaraan" required class="ui-select">
                        @foreach(['Motor', 'Mobil', 'Sepeda'] as $j)
                            <option value="{{ $j }}" @selected(old('jenis_kendaraan', $paket->jenis_kendaraan) === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Durasi Hari --}}
                <div>
                    <label class="ui-label">Durasi Masa Aktif (Hari) <span class="text-rose-500">*</span></label>
                    <input type="number" name="durasi_hari" min="1" max="365" value="{{ old('durasi_hari', $paket->durasi_hari ?? 30) }}" required class="ui-input">
                    <p class="text-[11px] text-slate-400 mt-1">Default 30 hari untuk 1 bulan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Batas Masuk Harian --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="ui-label mb-0">Batas Masuk Harian</label>
                        <span class="text-[10.5px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Batas Harian</span>
                    </div>
                    <input type="number" name="batas_masuk_harian" min="1" max="50" value="{{ old('batas_masuk_harian', $paket->batas_masuk_harian) }}" placeholder="Kosongkan jika Bebas Masuk" class="ui-input">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Contoh: <code>1</code> untuk maksimal 1x masuk per hari. Kosongkan jika bebas keluar-masuk sepuasnya.
                    </p>
                </div>

                {{-- Harga Paket --}}
                <div>
                    <label class="ui-label">Harga Paket (Rp) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-primary select-none">Rp</span>
                        <input type="text" inputmode="numeric" name="harga_paket" value="{{ old('harga_paket', isset($paket->harga_paket) ? number_format((float) $paket->harga_paket, 0, ',', '.') : '50.000') }}" required class="ui-input font-bold text-primary pl-10 input-ribuan">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Nominal yang ditagihkan saat registrasi.</p>
                </div>
            </div>

            {{-- Aturan Jika Batas Harian Habis --}}
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 space-y-1">
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Aturan Jika Batas Harian Habis</span>
                </div>
                <p class="text-[11.5px] leading-relaxed text-slate-500">
                    Jika member dengan batas masuk harian (misal maksimal 1x) masuk lagi di hari yang sama, palang gerbang <strong>tetap terbuka</strong> dan sistem otomatis mengenakan tarif parkir biasa (reguler).
                </p>
            </div>

            {{-- Status Aktif Checkbox --}}
            <div class="pt-1">
                <label class="flex items-center gap-2.5 text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $paket->status_aktif ?? 1)) class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary">
                    <span>Paket Aktif & Ditampilkan di Katalog Member</span>
                </label>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                <button type="submit" class="ui-btn ui-btn-primary text-xs">
                    {{ $paket->exists ? 'Simpan Perubahan' : 'Buat Paket Sekarang' }}
                </button>
                <a href="{{ route('admin.paket-langganan.index') }}" class="ui-btn ui-btn-secondary text-xs">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
