@extends('layouts.app')
@section('title', 'Self Check-In — ' . $tempatParkir->nama_tempat)

@section('content')
<div class="min-h-screen bg-slate-900 text-white flex flex-col justify-between p-4 sm:p-6 font-sans">
    
    {{-- Top Bar --}}
    <header class="max-w-md mx-auto w-full py-2 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('checkin.scanner') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/70 hover:text-white transition py-1 px-2.5 rounded-lg hover:bg-white/10">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Pindai Ulang</span>
            </a>
            @if(auth('pengendara')->check())
            <a href="{{ route('pengendara.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-white/70 hover:text-white transition py-1 px-2 rounded-lg hover:bg-white/10">
                <span>Dashboard</span>
            </a>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-emerald-400 border border-white/10">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Pos Gerbang Terhubung</span>
            </span>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 flex flex-col items-center justify-center my-auto py-4">

        <div class="w-full max-w-md bg-white text-dark rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
            
            {{-- Header Box --}}
            <div class="bg-primary text-white p-6 relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-36 h-36 rounded-full bg-primary-light/30 blur-2xl pointer-events-none"></div>

                <div class="flex items-start justify-between relative z-10">
                    <div>
                        @if($tempatParkir->kode_qr_gate)
                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-white/20 text-white font-mono text-[10px] font-bold tracking-wider uppercase mb-1.5">
                            {{ $tempatParkir->kode_qr_gate }}
                        </span>
                        @endif
                        <h1 class="font-heading font-black text-xl sm:text-2xl leading-tight">
                            {{ $tempatParkir->nama_tempat }}
                        </h1>
                        <p class="text-xs text-white/80 mt-1 font-sans">
                            Gerbang Masuk Mandiri &bull; Fast-Track Member & Reguler
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shrink-0 border border-white/20">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                </div>

                {{-- Capacity Metric --}}
                <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between text-xs">
                    <span class="text-white/80">Kapasitas Slot Reguler:</span>
                    <span class="font-bold {{ $sisaSlot > 0 ? 'text-accent' : 'text-rose-300' }}">
                        Sisa {{ $sisaSlot }} dari {{ $tempatParkir->kapasitas_reguler }} slot
                    </span>
                </div>
            </div>

            {{-- Form Body --}}
            <div class="p-6">
                @if(session('success'))
                <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2 text-emerald-800 text-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('warning'))
                <div class="mb-5 p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-2 text-amber-800 text-xs">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">
                    <p class="font-semibold">{{ $errors->first() }}</p>
                </div>
                @endif

                @if($sisaSlot <= 0)
                <div class="p-5 text-center bg-rose-50 border border-rose-200 rounded-xl text-rose-800">
                    <p class="font-heading font-bold text-base">Kapasitas Parkir Penuh</p>
                    <p class="text-xs mt-1 text-rose-600">Mohon menunggu hingga ada kendaraan yang keluar gerbang.</p>
                </div>
                @elseif(empty($supportedVehicles))
                <div class="p-5 text-center bg-amber-50 border border-amber-200 rounded-xl text-amber-800">
                    <p class="font-heading font-bold text-base">Layanan Belum Tersedia</p>
                    <p class="text-xs mt-1 text-amber-600">Tempat parkir ini belum mengaktifkan skema tarif kendaraan apapun.</p>
                </div>
                @else
                <form method="POST" action="{{ route('checkin.store', ['id_tempat_parkir' => $tempatParkir->id_tempat_parkir]) }}" class="space-y-5">
                    @csrf

                    {{-- Jenis Kendaraan Selector (Dinamis Sesuai Tarif Aktif Tenant) --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-dark/70 mb-2">
                            Pilih Jenis Kendaraan
                            <span class="text-[11px] font-normal text-slate-400 capitalize">(Layanan yang disediakan)</span>
                        </label>

                        <div class="grid grid-cols-{{ count($supportedVehicles) > 1 ? count($supportedVehicles) : 1 }} gap-2.5">
                            @foreach($supportedVehicles as $idx => $jenis)
                            @php
                                $t = $supportedTarifs[$jenis] ?? null;
                                $isDefault = old('jenis_kendaraan', $supportedVehicles[0] ?? '') === $jenis;
                                $biayaTampil = $t ? ($t->tipe_tarif === 'Flat Harian' ? 'Rp '.number_format($t->tarif_per_jam, 0, ',', '.').'/hari' : 'Rp '.number_format($t->tarif_jam_pertama > 0 ? $t->tarif_jam_pertama : $t->tarif_per_jam, 0, ',', '.').'/jam') : '';
                            @endphp
                            <label class="relative flex flex-col items-center text-center p-3 rounded-2xl border border-slate-200 cursor-pointer hover:border-primary/50 transition bg-secondary has-[:checked]:bg-primary/5 has-[:checked]:border-primary has-[:checked]:ring-2 has-[:checked]:ring-primary/20">
                                <input type="radio" name="jenis_kendaraan" value="{{ $jenis }}" class="sr-only" @checked($isDefault)>
                                
                                @if($jenis === 'Motor')
                                    <svg class="w-7 h-7 text-primary mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($jenis === 'Mobil')
                                    <svg class="w-7 h-7 text-primary mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8m-8 4h8m-4 8l-2 2m4-2l2 2m-6-4h8a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2h2z"/></svg>
                                @else
                                    <svg class="w-7 h-7 text-primary mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                @endif

                                <span class="text-xs font-black text-dark">{{ $jenis }}</span>
                                @if($biayaTampil)
                                <span class="text-[10px] font-semibold text-primary mt-0.5">{{ $biayaTampil }}</span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Plat Nomor Input --}}
                    <div>
                        {{-- Logged-In User Vehicle Quick-Pick Chips --}}
                        @if(auth('pengendara')->check() && auth('pengendara')->user()->kendaraan->isNotEmpty())
                        <div class="mb-3.5 p-3.5 bg-primary/5 border border-primary/20 rounded-2xl">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="font-bold text-primary flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Kendaraan di Akun Anda:
                                </span>
                                <span class="text-[10px] text-slate-500">Klik untuk isi otomatis</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(auth('pengendara')->user()->kendaraan as $vk)
                                <button type="button" 
                                    onclick="fillVehicle('{{ $vk->plat_nomor }}', '{{ $vk->jenis_kendaraan }}', '{{ $vk->warna ?? '' }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-primary/30 bg-white hover:bg-primary hover:text-white transition text-xs font-mono font-bold text-dark shadow-2xs group cursor-pointer">
                                    <span>{{ $vk->plat_nomor }}</span>
                                    <span class="text-[10px] opacity-70 group-hover:text-white font-sans font-medium">({{ $vk->jenis_kendaraan }})</span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center justify-between mb-1.5">
                            <label for="plat_nomor" class="block text-xs font-bold uppercase tracking-wider text-dark/70">
                                Nomor Plat Kendaraan
                            </label>
                            <span class="text-[10px] font-semibold text-primary bg-primary/10 px-2 py-0.5 rounded-full">
                                Standar Perpol No. 7/2021
                            </span>
                        </div>
                        <div class="relative">
                            <input type="text" id="plat_nomor" name="plat_nomor" value="{{ old('plat_nomor') }}" required autofocus
                                placeholder="B 1234 XYZ"
                                autocomplete="off"
                                maxlength="12"
                                class="w-full px-4 py-3.5 bg-slate-50 border-2 border-slate-300 rounded-xl font-mono text-xl font-black text-center tracking-widest uppercase focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition shadow-inner">
                        </div>

                        {{-- Real-time Plate Badge Preview --}}
                        <div class="mt-2.5 flex items-center justify-between bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                            <span class="text-[11px] text-slate-500 font-medium">Format TNKB:</span>
                            <div class="inline-flex items-center justify-center px-3 py-1 bg-black text-white font-mono font-black text-xs tracking-widest rounded-md border border-slate-700 shadow-sm">
                                <span id="plate-preview">{{ old('plat_nomor') ?: 'B _ _ _ _  _ _ _' }}</span>
                            </div>
                        </div>

                        <div class="p-2.5 mt-2 bg-blue-50 border border-blue-200/80 rounded-xl text-[11px] text-blue-900 leading-relaxed">
                            <strong>Fast-Track Otomatis:</strong> Jika plat ini terdaftar memiliki <em>Member Pass</em> atau <em>Booking Inap</em> aktif, sistem akan langsung menerbitkan Green Digital Pass (Rp 0) tanpa antre kasir.
                        </div>
                    </div>

                    {{-- Warna Kendaraan (Opsional) --}}
                    <div>
                        <label for="warna" class="block text-xs font-semibold text-dark/70 mb-1">Warna Kendaraan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" id="warna" name="warna" value="{{ old('warna') }}" placeholder="Contoh: Hitam / Putih / Merah" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:border-primary focus:outline-none transition">
                    </div>

                    {{-- Submit CTA --}}
                    <button type="submit" class="w-full py-4 bg-accent hover:bg-accent-dark text-dark font-heading font-black text-base rounded-2xl transition duration-200 active:scale-95 shadow-lg shadow-accent/25 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Terbitkan Karcis / Fast-Track QR</span>
                    </button>
                </form>
                @endif
            </div>

            {{-- Footer Info --}}
            <div class="p-4 bg-slate-50 border-t border-slate-100 text-center text-xs text-dark/60">
                <span>Sudah punya akun Pengendara atau Member?</span>
                <a href="{{ route('login') }}" class="text-primary font-bold hover:underline ml-1">Masuk di sini</a>
            </div>
        </div>
    </main>

    {{-- Footer Simple --}}
    <footer class="py-4 text-center text-xs text-white/50 font-sans">
        &copy; {{ date('Y') }} Parkirin &bull; Self Check-In Walk-In System
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const platInput = document.getElementById('plat_nomor');
    const platePreview = document.getElementById('plate-preview');

    /**
     * Format TNKB Indonesia:
     * [Wilayah 1-2 Huruf] [Nomor 1-4 Angka] [Seri 1-3 Huruf]
     */
    function formatTNKB(val) {
        let clean = val.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (!clean) return '';
        
        let wilayahMatch = clean.match(/^[A-Z]{1,2}/);
        if (!wilayahMatch) return clean.slice(0, 10);
        let wilayah = wilayahMatch[0];
        let rest1 = clean.slice(wilayah.length);
        if (!rest1) return wilayah;

        let nomorMatch = rest1.match(/^\d{1,4}/);
        if (!nomorMatch) return wilayah;
        let nomor = nomorMatch[0];
        let rest2 = rest1.slice(nomor.length);
        if (!rest2) return `${wilayah} ${nomor}`;

        let seriMatch = rest2.match(/^[A-Z]{1,3}/);
        let seri = seriMatch ? seriMatch[0] : '';
        return `${wilayah} ${nomor} ${seri}`.trim();
    }

    if (platInput) {
        platInput.addEventListener('input', function(e) {
            let cursor = this.selectionStart;
            let prevLen = this.value.length;
            let formatted = formatTNKB(this.value);
            this.value = formatted;
            
            if (platePreview) {
                platePreview.textContent = formatted || 'B _ _ _ _  _ _ _';
            }
        });

        if (platePreview && platInput.value) {
            platePreview.textContent = formatTNKB(platInput.value);
        }
    }

    window.fillVehicle = function(plat, jenis, warna) {
        if (platInput) {
            platInput.value = formatTNKB(plat);
            if (platePreview) {
                platePreview.textContent = platInput.value;
            }
        }
        if (warna) {
            const warnaInput = document.getElementById('warna');
            if (warnaInput && warna !== 'Tidak Tercatat') {
                warnaInput.value = warna;
            }
        }
        const radio = document.querySelector(`input[name="jenis_kendaraan"][value="${jenis}"]`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    };
});
</script>
@endsection
