@extends('layouts.staff')
@section('title', ($tarif->exists ? 'Edit' : 'Tambah').' Tarif')
@section('page-title', ($tarif->exists ? 'Edit' : 'Tambah').' Tarif')
@section('page-desc', 'Atur skema tarif parkir reguler, inap harian, dan denda kehilangan tiket.')

@section('staff-content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-heading font-extrabold text-base text-[#212121]">
                    {{ $tarif->exists ? 'Edit Skema Tarif' : 'Tambah Skema Tarif Baru' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Form dinamis menyesuaikan tipe tarif yang dipilih.</p>
            </div>
            <a href="{{ route('admin.tarif.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
                &larr; Kembali
            </a>
        </div>

        <form method="POST" action="{{ $tarif->exists ? route('admin.tarif.update', $tarif) : route('admin.tarif.store') }}" class="p-6 space-y-5">
            @csrf
            @if($tarif->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">Jenis Kendaraan <span class="text-rose-500">*</span></label>
                    <select name="jenis_kendaraan" required class="ui-select">
                        @foreach(['Motor', 'Mobil', 'Sepeda'] as $j)
                            <option value="{{ $j }}" @selected(old('jenis_kendaraan', $tarif->jenis_kendaraan) === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label">Tipe Tarif <span class="text-rose-500">*</span></label>
                    <select name="tipe_tarif" id="tipe_tarif" required class="ui-select">
                        @foreach(['Flat Harian', 'Per Jam', 'Progresif Mal'] as $t)
                            <option value="{{ $t }}" @selected(old('tipe_tarif', $tarif->tipe_tarif ?? 'Flat Harian') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Bidang Tarif Flat / Per Jam --}}
            <div id="field-perjam" class="tarif-group">
                <label class="ui-label">Tarif Dasar / Flat (Rp)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 select-none">Rp</span>
                    <input type="text" inputmode="numeric" name="tarif_per_jam" value="{{ old('tarif_per_jam', isset($tarif->tarif_per_jam) ? number_format((float) $tarif->tarif_per_jam, 0, ',', '.') : '0') }}" class="ui-input font-bold text-slate-800 pl-10 input-ribuan">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Digunakan sebagai tarif flat per kedatangan atau acuan per jam.</p>
            </div>

            {{-- Bidang Tarif Progresif --}}
            <div id="field-progresif" class="tarif-group grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">Tarif Jam Pertama (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 select-none">Rp</span>
                        <input type="text" inputmode="numeric" name="tarif_jam_pertama" value="{{ old('tarif_jam_pertama', isset($tarif->tarif_jam_pertama) ? number_format((float) $tarif->tarif_jam_pertama, 0, ',', '.') : '0') }}" class="ui-input font-bold text-slate-800 pl-10 input-ribuan">
                    </div>
                </div>
                <div>
                    <label class="ui-label">Tarif Jam Berikutnya (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 select-none">Rp</span>
                        <input type="text" inputmode="numeric" name="tarif_berikutnya" value="{{ old('tarif_berikutnya', isset($tarif->tarif_berikutnya) ? number_format((float) $tarif->tarif_berikutnya, 0, ',', '.') : '0') }}" class="ui-input font-bold text-slate-800 pl-10 input-ribuan">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                {{-- Tarif Inap Harian --}}
                <div>
                    <label class="ui-label">Tarif Inap Harian (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400 select-none">Rp</span>
                        <input type="text" inputmode="numeric" name="tarif_inap_harian" value="{{ old('tarif_inap_harian', isset($tarif->tarif_inap_harian) ? number_format((float) $tarif->tarif_inap_harian, 0, ',', '.') : '0') }}" class="ui-input font-bold text-slate-800 pl-10 input-ribuan">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Dikenakan bila kendaraan menginap melewati batas operasional harian.</p>
                </div>

                {{-- Denda Karcis Hilang --}}
                <div>
                    <label class="ui-label">Denda Tiket / Karcis Hilang (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-rose-400 select-none">Rp</span>
                        <input type="text" inputmode="numeric" name="denda_karcis_hilang" value="{{ old('denda_karcis_hilang', isset($tarif->denda_karcis_hilang) ? number_format((float) $tarif->denda_karcis_hilang, 0, ',', '.') : '0') }}" class="ui-input font-bold text-rose-600 pl-10 input-ribuan">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Dikenakan petugas kasir saat pengendara kehilangan karcis digital/fisik.</p>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2.5 text-xs text-slate-700 font-semibold cursor-pointer select-none">
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $tarif->status_aktif ?? 1)) class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary">
                    <span>Skema Tarif Aktif</span>
                </label>
            </div>

            <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                <button type="submit" class="ui-btn ui-btn-primary text-xs">Simpan Tarif</button>
                <a href="{{ route('admin.tarif.index') }}" class="ui-btn ui-btn-secondary text-xs">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const sel = document.getElementById('tipe_tarif');
    const perjam = document.getElementById('field-perjam');
    const prog = document.getElementById('field-progresif');
    function sync(){
        const v = sel.value;
        if(v === 'Progresif Mal') {
            perjam.style.display = 'none';
            prog.style.display = '';
        } else {
            perjam.style.display = '';
            prog.style.display = 'none';
        }
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
