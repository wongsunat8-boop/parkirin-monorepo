@extends('layouts.staff')
@section('title', ($tarif->exists?'Edit':'Tambah').' Tarif')
@section('sidebar') @include('admin.partials.sidebar') @endsection
@section('staff-content')
    <h1 class="page-title">{{ $tarif->exists?'Edit':'Tambah' }} Tarif</h1>
    <p class="text-sm text-gray-500 mb-4">Form dinamis menyesuaikan tipe tarif. Tarif inap dapat diisi untuk semua tipe.</p>
    <form method="POST" action="{{ $tarif->exists ? route('admin.tarif.update',$tarif) : route('admin.tarif.store') }}" class="ui-card ui-card-b max-w-3xl space-y-4">
        @csrf @if($tarif->exists) @method('PUT') @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="ui-label">Jenis Kendaraan</label>
                <select name="jenis_kendaraan" required class="ui-input">
                    @foreach(['Motor','Mobil','Sepeda'] as $j)<option value="{{ $j }}" @selected(old('jenis_kendaraan',$tarif->jenis_kendaraan)===$j)>{{ $j }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="ui-label">Tipe Tarif</label>
                <select name="tipe_tarif" id="tipe_tarif" required class="ui-input">
                    @foreach(['Per Jam','Flat Harian','Progresif Mal'] as $t)<option value="{{ $t }}" @selected(old('tipe_tarif',$tarif->tipe_tarif ?? 'Per Jam')===$t)>{{ $t }}</option>@endforeach
                </select>
            </div>
        </div>
        <div id="field-perjam" class="tarif-group">
            <label class="ui-label">Tarif Per Jam (Rp)</label>
            <input type="number" step="0.01" min="0" name="tarif_per_jam" value="{{ old('tarif_per_jam',$tarif->tarif_per_jam ?? 0) }}" class="ui-input">
        </div>
        <div id="field-progresif" class="tarif-group grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="ui-label">Tarif Jam Pertama (Rp)</label><input type="number" step="0.01" min="0" name="tarif_jam_pertama" value="{{ old('tarif_jam_pertama',$tarif->tarif_jam_pertama ?? 0) }}" class="ui-input"></div>
            <div><label class="ui-label">Tarif Jam Berikutnya (Rp)</label><input type="number" step="0.01" min="0" name="tarif_berikutnya" value="{{ old('tarif_berikutnya',$tarif->tarif_berikutnya ?? 0) }}" class="ui-input"></div>
        </div>
        <div>
            <label class="ui-label">Tarif Inap (Rp, opsional/tambahan)</label>
            <input type="number" step="0.01" min="0" name="tarif_inap" value="{{ old('tarif_inap',$tarif->tarif_inap ?? 0) }}" class="ui-input">
            <p class="text-xs text-gray-500 mt-1">Berlaku sebagai komponen tambahan sesuai skema tarif inap.</p>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif',$tarif->status_aktif ?? 1))> Aktif</label>
        <div class="flex gap-2"><button class="ui-btn ui-btn-primary">Simpan</button><a href="{{ route('admin.tarif.index') }}" class="ui-btn ui-btn-secondary">Batal</a></div>
    </form>
@endsection
@push('scripts')
<script>
(function(){
    const sel = document.getElementById('tipe_tarif');
    const perjam = document.getElementById('field-perjam');
    const prog = document.getElementById('field-progresif');
    function sync(){
        const v = sel.value;
        perjam.style.display = (v === 'Per Jam' || v === 'Flat Harian') ? '' : 'none';
        prog.style.display = (v === 'Progresif Mal') ? '' : 'none';
    }
    sel.addEventListener('change', sync); sync();
})();
</script>
@endpush
