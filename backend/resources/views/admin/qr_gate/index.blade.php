@extends('layouts.staff')
@section('title', 'QR Gerbang Masuk — ' . $tenant->nama_tempat)
@section('page-title', 'QR Gerbang Masuk')
@section('page-desc', 'QR Code khusus pos masuk fisik untuk melayani Self Check-In pengunjung (Guest & Member).')

@section('staff-content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm shadow-xs">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-sm shadow-xs">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span class="font-medium">{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Policy Banner --}}
    <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed">
            <p class="font-bold text-slate-900 mb-0.5">Ketentuan Pembuatan QR Gerbang</p>
            <p>QR Code ini bertindak sebagai token pengenal fisik gerbang masuk tempat parkir <strong>{{ $tenant->nama_tempat }}</strong>. Sesuai arsitektur keamanan Parkirin, QR ini <strong>hanya dapat digenerate 1 (satu) kali per tenant</strong> dan akan terkunci permanen agar tidak disalahgunakan.</p>
        </div>
    </div>

    @if(!$tenant->hasGateQr())
    {{-- ═══════════════════════════════════════════
        STATE 1: QR BELUM DIGENERATE
    ═══════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 text-center shadow-xs">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-200/60 shadow-xs">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h3 class="font-heading font-black text-xl text-slate-900 mb-1">QR Gerbang Belum Dibuat</h3>
        <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">
            Klik tombol di bawah ini untuk menerbitkan QR Code resmi tempat parkir Anda. Setelah dibuat, Anda dapat mengunduh dan mencetak standee untuk ditempel di pos gerbang masuk.
        </p>

        <form method="POST" action="{{ route('admin.qr-gate.generate') }}" onsubmit="return confirm('Apakah Anda yakin ingin men-generate QR Gerbang Masuk? Kode QR ini hanya dapat dibuat satu kali dan akan terkunci permanen untuk tenant ini.')">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-primary hover:bg-primary-dark text-white font-bold text-sm rounded-xl shadow-sm hover:shadow transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Generate QR Gerbang Masuk (1x Saja)</span>
            </button>
        </form>
    </div>

    @else
    {{-- ═══════════════════════════════════════════
        STATE 2: QR SUDAH DIGENERATE & TERKUNCI
    ═══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left: QR Card Preview --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 text-center shadow-xs flex flex-col items-center justify-between">
            <div class="w-full">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70 mb-4">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>QR Resmi Aktif dan Terkunci</span>
                </div>

                {{-- QR Canvas & Fallback Container --}}
                <div class="p-4 bg-white rounded-2xl border-2 border-dashed border-slate-200 shadow-inner inline-flex items-center justify-center my-2 min-w-[240px] min-h-[240px]" id="qr-container">
                    <div id="qrcode" class="mx-auto flex items-center justify-center"></div>
                    <img id="qr-fallback" 
                         src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($checkInUrl) }}" 
                         alt="QR Code {{ $tenant->kode_qr_gate }}" 
                         class="w-[220px] h-[220px] rounded-lg hidden"
                         onload="if(!document.querySelector('#qrcode canvas')) { this.classList.remove('hidden'); }" />
                </div>

                <div class="mt-3">
                    <p class="font-heading font-black text-slate-900 text-lg">{{ $tenant->nama_tempat }}</p>
                    <p class="font-mono text-xs font-bold text-primary tracking-wide mt-0.5">{{ $tenant->kode_qr_gate }}</p>
                </div>
            </div>

            {{-- Quick Download & Print Buttons --}}
            <div class="w-full pt-4 mt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5">
                <button type="button" onclick="downloadQr()" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh PNG</span>
                </button>
                <a href="{{ route('admin.qr-gate.print') }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-accent hover:bg-accent-dark text-slate-950 text-xs font-bold rounded-xl transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Standee</span>
                </a>
            </div>
        </div>

        {{-- Right: Technical Details & Audit Info --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs space-y-4">
                <h4 class="font-heading font-black text-base text-slate-900 pb-3 border-b border-slate-100 flex items-center justify-between">
                    <span>Informasi Teknis Pos Gerbang</span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">ID #{{ $tenant->id_tempat_parkir }}</span>
                </h4>

                <div class="space-y-3 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-500 block text-xs font-semibold uppercase tracking-wider mb-1">Target URL Check-In</span>
                        <div class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <span class="font-mono text-xs text-slate-700 truncate select-all flex-1">{{ $checkInUrl }}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $checkInUrl }}'); alert('Tautan Check-In berhasil disalin!');" class="p-1.5 hover:bg-white rounded-lg text-slate-500 hover:text-primary transition shrink-0 cursor-pointer" title="Salin Tautan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-slate-500 text-[11px] font-semibold uppercase">Waktu Pembuatan</p>
                            <p class="font-bold text-slate-800 text-xs mt-0.5">{{ $tenant->qr_generated_at ? \Carbon\Carbon::parse($tenant->qr_generated_at)->isoFormat('D MMM Y, HH:mm') : 'Tercatat' }} WIB</p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-slate-500 text-[11px] font-semibold uppercase">Status Pembuatan Ulang</p>
                            <p class="font-bold text-rose-600 text-xs mt-0.5">Terkunci (Maks 1x)</p>
                        </div>
                    </div>
                </div>

                {{-- Cara Penggunaan --}}
                <div class="pt-3 border-t border-slate-100">
                    <p class="font-heading font-bold text-xs uppercase tracking-wider text-slate-500 mb-2">Alur Penggunaan di Lapangan</p>
                    <ol class="space-y-1.5 text-xs text-slate-600 list-decimal pl-4">
                        <li>Cetak poster standee melalui tombol <strong>Cetak Standee</strong>.</li>
                        <li>Tempatkan standee di tiang palang pintu masuk atau loket gerbang.</li>
                        <li>Pengunjung tanpa login (guest) mengklik tombol <strong>Scan QR Check-In</strong> di HP mereka dan mengarahkan kamera ke standee ini.</li>
                        <li>Form input kendaraan terbuka otomatis khusus untuk lokasi <strong>{{ $tenant->nama_tempat }}</strong>.</li>
                    </ol>
                </div>
            </div>

            {{-- Disabled Re-generate Button --}}
            <div class="p-4 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Pembuatan QR baru dinonaktifkan (Keamanan 1x Generate)</span>
                </span>
                <span class="font-mono font-bold text-slate-600">LOCKED</span>
            </div>
        </div>

    </div>
    @endif

</div>

{{-- QR Code Library Local + CDN Fallback --}}
<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
<script>
    function renderQr() {
        const target = document.getElementById('qrcode');
        const fallback = document.getElementById('qr-fallback');
        if (!target) return;

        try {
            if (typeof QRCode !== 'undefined') {
                target.innerHTML = '';
                new QRCode(target, {
                    text: "{{ $checkInUrl }}",
                    width: 220,
                    height: 220,
                    colorDark: "#0F172A",
                    colorLight: "#FFFFFF",
                    correctLevel: QRCode.CorrectLevel.H
                });
                setTimeout(function() {
                    const canvas = target.querySelector('canvas');
                    const img = target.querySelector('img');
                    if (canvas) canvas.className = 'mx-auto rounded-lg shadow-2xs';
                    if (img) img.className = 'mx-auto rounded-lg shadow-2xs';
                }, 50);
            } else if (fallback) {
                fallback.classList.remove('hidden');
            }
        } catch (e) {
            console.error('QR Render Error:', e);
            if (fallback) fallback.classList.remove('hidden');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderQr);
    } else {
        renderQr();
    }

    function downloadQr() {
        const container = document.getElementById('qrcode');
        const fallback = document.getElementById('qr-fallback');
        let src = null;

        if (container) {
            const canvas = container.querySelector('canvas');
            const img = container.querySelector('img');
            if (canvas) src = canvas.toDataURL('image/png');
            else if (img && img.src) src = img.src;
        }

        if (!src && fallback && fallback.src) {
            src = fallback.src;
        }

        if (src) {
            const link = document.createElement('a');
            link.download = 'QR-Gerbang-{{ Str::slug($tenant->nama_tempat) }}.png';
            link.href = src;
            link.click();
        } else {
            alert('QR Code belum selesai dimuat.');
        }
    }
</script>
@endsection
