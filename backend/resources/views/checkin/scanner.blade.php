@extends('layouts.app')
@section('title', 'Pemindai QR Gerbang Masuk — Parkirin')

@section('content')
<div class="min-h-screen bg-slate-950 text-white flex flex-col justify-between font-sans relative overflow-hidden">
    
    {{-- Background glowing accents --}}
    <div class="absolute inset-0 pointer-events-none opacity-20">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-primary blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-accent blur-3xl"></div>
    </div>

    {{-- Top Navigation Bar --}}
    <header class="relative z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur-md">
        <div class="max-w-2xl mx-auto px-4 py-3.5 flex items-center justify-between">
            @if(auth('pengendara')->check())
            <a href="{{ route('pengendara.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white transition py-1 px-2.5 rounded-lg hover:bg-white/10">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard Pengendara</span>
            </a>
            @else
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-bold text-white/70 hover:text-white transition py-1 px-2.5 rounded-lg hover:bg-white/10">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
            @endif
            <div class="flex items-center gap-2">
                @if(auth('pengendara')->check())
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-primary/30 border border-primary/50 text-white font-sans">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span class="truncate max-w-[120px]">{{ auth('pengendara')->user()->nama_lengkap }}</span>
                </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 border border-white/15 text-accent font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>High-Speed Scanner</span>
                </span>
            </div>
        </div>
    </header>

    {{-- Main Viewport --}}
    <main class="relative z-10 flex-1 flex flex-col items-center justify-start p-4 sm:p-6 max-w-lg mx-auto w-full space-y-5">
        
        {{-- Instruction Header --}}
        <div class="text-center pt-2">
            <h1 class="font-heading font-black text-2xl sm:text-3xl text-white tracking-tight">
                Pindai QR Gerbang Masuk
            </h1>
            <p class="text-xs sm:text-sm text-white/70 mt-1 max-w-sm mx-auto leading-relaxed">
                Arahkan kamera ke QR Code di pos masuk untuk mengisi data kendaraan & menerbitkan tiket digital.
            </p>
        </div>

        {{-- Camera Scanner Frame --}}
        <div class="w-full bg-slate-900 rounded-3xl border-2 border-white/15 shadow-2xl overflow-hidden relative p-3 sm:p-4">
            
            {{-- Camera Viewport Box --}}
            <div class="relative w-full aspect-square max-h-[340px] bg-black rounded-2xl overflow-hidden flex items-center justify-center border border-white/10" id="scanner-wrapper">
                
                {{-- HTML5-QRCode Reader Element --}}
                <div id="qr-reader" class="w-full h-full"></div>

                {{-- Corner Finder Guides (E-Wallet Style Reticle) --}}
                <div class="pointer-events-none absolute inset-6 sm:inset-8 border-2 border-transparent z-10" id="finder-guides">
                    <span class="absolute top-0 left-0 w-7 h-7 border-t-4 border-l-4 border-accent rounded-tl-xl shadow-[0_0_8px_rgba(255,215,23,0.5)]"></span>
                    <span class="absolute top-0 right-0 w-7 h-7 border-t-4 border-r-4 border-accent rounded-tr-xl shadow-[0_0_8px_rgba(255,215,23,0.5)]"></span>
                    <span class="absolute bottom-0 left-0 w-7 h-7 border-b-4 border-l-4 border-accent rounded-bl-xl shadow-[0_0_8px_rgba(255,215,23,0.5)]"></span>
                    <span class="absolute bottom-0 right-0 w-7 h-7 border-b-4 border-r-4 border-accent rounded-br-xl shadow-[0_0_8px_rgba(255,215,23,0.5)]"></span>
                    
                    {{-- High-Tech Animated Laser Line --}}
                    <div id="laser-line" class="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-accent to-transparent shadow-[0_0_14px_#FFD717] animate-laser"></div>
                </div>

                {{-- Camera Loading Overlay --}}
                <div id="scanner-loading" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-xs text-white/80 space-y-3 z-30">
                    <div class="w-10 h-10 border-3 border-accent border-t-transparent rounded-full animate-spin"></div>
                    <p class="font-semibold text-white">Menghubungkan Kamera...</p>
                    <p class="text-[11px] text-white/60 max-w-xs">Izinkan akses kamera di browser Anda untuk mulai mendeteksi QR secara otomatis.</p>
                </div>

                {{-- Success Flash Overlay --}}
                <div id="scanner-success" class="hidden absolute inset-0 bg-emerald-500/95 backdrop-blur-md flex-col items-center justify-center p-6 text-center z-40 transition-all duration-200">
                    <div class="w-16 h-16 rounded-full bg-white text-emerald-600 flex items-center justify-center mb-3 shadow-xl animate-bounce">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="font-heading font-black text-xl text-white">QR Berhasil Terbaca!</p>
                    <p class="text-xs text-white/90 mt-1">Membuka formulir kendaraan...</p>
                </div>

            </div>

            {{-- Helpful scanning tips when scanning from phone/laptop screen --}}
            <div class="mt-2.5 px-3 py-2 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2 text-[11px] text-white/70">
                <span class="text-accent shrink-0">💡</span>
                <span class="leading-snug"><strong>Tips:</strong> Jaga jarak sekitar <strong>25–40 cm</strong> dan pastikan QR tidak silau/berbayang.</span>
            </div>

            {{-- Scanner Controls (Switch camera, Torch, File upload) --}}
            <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                <button type="button" id="btn-switch-camera" onclick="switchCamera()" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white/90 font-medium transition border border-white/10 cursor-pointer">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Ganti Kamera</span>
                </button>
                <button type="button" id="btn-torch" onclick="toggleTorch()" class="hidden inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white/90 font-medium transition border border-white/10 cursor-pointer">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span id="torch-label">Senter</span>
                </button>
                <label for="qr-input-file" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white/90 font-medium transition border border-white/10 cursor-pointer">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Upload Foto QR</span>
                    <input type="file" id="qr-input-file" accept="image/*" class="sr-only" onchange="scanImageFile(this)">
                </label>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════
            MOBILE-FIRST SECTION: PILIH LOKASI MITRA PARKIRIN
        ═══════════════════════════════════════════ --}}
        @if(isset($activeTenants) && count($activeTenants) > 0)
        <div class="w-full bg-slate-900 rounded-3xl border border-white/15 p-4 sm:p-5 shadow-xl space-y-3">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-sm sm:text-base text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Pilih Lokasi Mitra Parkir</span>
                    </h2>
                    <p class="text-[11px] text-white/60">Ketuk lokasi pos parkir Anda jika kamera bermasalah</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-accent border border-white/15 font-mono">
                    {{ count($activeTenants) }} Lokasi
                </span>
            </div>

            {{-- Quick Filter Input (Realtime client-side filter) --}}
            @if(count($activeTenants) > 1)
            <div class="relative">
                <svg class="w-3.5 h-3.5 text-white/40 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="tenant-filter" oninput="filterTenants(this.value)" placeholder="Cari nama lokasi atau stasiun..." class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-white/15 rounded-xl text-xs text-white placeholder:text-white/40 focus:border-accent focus:outline-none transition">
            </div>
            @endif

            {{-- Vertical List of Touch-Friendly Cards --}}
            <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-0.5 scrollbar-thin" id="tenant-list">
                @foreach($activeTenants as $t)
                @php
                    $terisi = $t->terisi_count ?? 0;
                    $kapasitas = $t->kapasitas_reguler ?? 0;
                    $sisa = max(0, $kapasitas - $terisi);
                    $isFull = $kapasitas > 0 && $sisa <= 0;
                    $targetUrl = route('checkin.public', ['id_tempat_parkir' => $t->kode_qr_gate ?? $t->id_tempat_parkir]);
                    $layanan = $t->tarif ? $t->tarif->pluck('jenis_kendaraan')->unique()->values()->all() : [];
                @endphp
                <a href="{{ $targetUrl }}" 
                   data-tenant-name="{{ strtolower($t->nama_tempat) }}"
                   class="tenant-item group relative flex items-center justify-between p-3.5 rounded-2xl bg-white/5 hover:bg-white/10 active:bg-white/15 border border-white/10 hover:border-accent/50 transition duration-150 text-left cursor-pointer">
                    
                    <div class="flex items-center gap-3 min-w-0 flex-1 pr-2">
                        {{-- Icon Pin --}}
                        <div class="w-10 h-10 rounded-xl bg-primary/20 text-accent flex items-center justify-center shrink-0 border border-primary/30 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        
                        {{-- Title & Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h3 class="font-heading font-black text-sm text-white group-hover:text-accent transition truncate">
                                    {{ $t->nama_tempat }}
                                </h3>
                                @if($t->kode_qr_gate)
                                <span class="px-1.5 py-0.2 rounded bg-white/10 font-mono text-[9px] text-white/70">
                                    {{ $t->kode_qr_gate }}
                                </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 mt-1 text-[11px] flex-wrap">
                                {{-- Slot status --}}
                                @if($isFull)
                                    <span class="inline-flex items-center gap-1 text-rose-400 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Penuh (0 Slot)</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>Sisa {{ $sisa }} Slot</span>
                                    </span>
                                @endif

                                {{-- Vehicle Badges --}}
                                @if(count($layanan) > 0)
                                <span class="text-white/30">&bull;</span>
                                <div class="flex items-center gap-1 text-[10px] text-white/60">
                                    @foreach($layanan as $j)
                                    <span class="px-1.5 py-0.2 rounded bg-white/5 border border-white/10">{{ $j }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- CTA Arrow Pill --}}
                    <div class="w-8 h-8 rounded-full bg-white/10 group-hover:bg-accent group-hover:text-slate-950 text-white flex items-center justify-center shrink-0 transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>

                </a>
                @endforeach
            </div>

            <p id="no-tenant-match" class="hidden text-center text-xs text-white/50 py-4 italic">
                Tidak ada lokasi parkir yang sesuai dengan pencarian Anda.
            </p>

        </div>
        @endif

        {{-- Manual Code Input (Minimalist Collapsible) --}}
        <details class="w-full group bg-slate-900/60 rounded-2xl border border-white/10 overflow-hidden text-xs">
            <summary class="p-3.5 flex items-center justify-between cursor-pointer select-none text-white/70 hover:text-white transition">
                <span class="flex items-center gap-2 font-medium">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Punya Kode Gerbang Khusus? Input Manual</span>
                </span>
                <span class="text-white/40 group-open:rotate-180 transition-transform duration-200">&darr;</span>
            </summary>
            <div class="p-3.5 pt-0 border-t border-white/5">
                <form id="form-manual-code" onsubmit="event.preventDefault(); goToCode();" class="flex gap-2 mt-2">
                    <input type="text" id="manual-code-input" placeholder="Contoh: PKR-GATE-001-GJQJ18" class="flex-1 px-3.5 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-xs text-white placeholder:text-white/40 font-mono uppercase focus:border-accent focus:outline-none">
                    <button type="submit" class="px-4 py-2.5 bg-accent hover:bg-accent-dark text-slate-950 font-bold text-xs rounded-xl transition cursor-pointer">
                        Buka
                    </button>
                </form>
            </div>
        </details>

    </main>

    {{-- Footer --}}
    <footer class="relative z-20 py-4 text-center text-xs text-white/40 font-sans border-t border-white/5">
        &copy; {{ date('Y') }} Parkirin &bull; Sistem Pemindai QR Gerbang Mandiri
    </footer>

</div>

@push('styles')
<style>
    @keyframes laser {
        0% { top: 12%; opacity: 0.8; }
        50% { top: 88%; opacity: 1; }
        100% { top: 12%; opacity: 0.8; }
    }
    .animate-laser {
        animation: laser 1.8s ease-in-out infinite;
    }
    #qr-reader {
        border: none !important;
        position: relative;
    }
    #qr-reader video {
        border-radius: 1rem;
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
    }
    #qr-reader__scan_region {
        background: transparent !important;
    }
    #qr-reader__dashboard {
        display: none !important;
    }
</style>
@endpush

{{-- Load Local HTML5-QRCode with CDN fallback --}}
<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script>
    if (typeof Html5Qrcode === 'undefined') {
        document.write('<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>');
    }
</script>

<script>
    let html5QrCode = null;
    let currentCameraIndex = 0;
    let cameraList = [];
    let isProcessing = false;
    let isTorchOn = false;

    // Pleasant 2-tone melodic chime feedback on scan success (like e-wallet / payment app)
    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;

            // Tone 1: E6 (1318.5 Hz)
            const o1 = ctx.createOscillator();
            const g1 = ctx.createGain();
            o1.type = 'sine';
            o1.frequency.setValueAtTime(1318.5, now);
            g1.gain.setValueAtTime(0.15, now);
            g1.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
            o1.connect(g1);
            g1.connect(ctx.destination);
            o1.start(now);
            o1.stop(now + 0.08);

            // Tone 2: A6 (1760 Hz)
            const o2 = ctx.createOscillator();
            const g2 = ctx.createGain();
            o2.type = 'sine';
            o2.frequency.setValueAtTime(1760, now + 0.08);
            g2.gain.setValueAtTime(0.20, now + 0.08);
            g2.gain.exponentialRampToValueAtTime(0.001, now + 0.22);
            o2.connect(g2);
            g2.connect(ctx.destination);
            o2.start(now + 0.08);
            o2.stop(now + 0.22);
        } catch (e) {}
    }

    // Extract destination URL or Gate token safely
    function extractDestination(decodedText) {
        const raw = (decodedText || '').trim();

        // Check if contains /in/{code_or_id}
        const match = raw.match(/\/in\/([A-Za-z0-9\-_]+)/);
        if (match && match[1]) {
            return '{{ url("/in") }}/' + match[1];
        }

        // If raw gate token (e.g. PKR-GATE-001-GJQJ18)
        if (!raw.startsWith('http://') && !raw.startsWith('https://')) {
            return '{{ url("/in") }}/' + encodeURIComponent(raw);
        }

        return raw;
    }

    function onScanSuccess(decodedText) {
        if (isProcessing) return;
        isProcessing = true;
        playBeep();

        const successOverlay = document.getElementById('scanner-success');
        if (successOverlay) {
            successOverlay.classList.remove('hidden');
            successOverlay.classList.add('flex');
        }

        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(() => {});
        }

        const destination = extractDestination(decodedText);

        setTimeout(() => {
            window.location.href = destination;
        }, 500);
    }

    function getScannerConfig() {
        return {
            fps: 25,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                const edge = Math.min(viewfinderWidth, viewfinderHeight);
                const boxSize = Math.max(200, Math.min(320, Math.floor(edge * 0.78)));
                return { width: boxSize, height: boxSize };
            },
            aspectRatio: 1.0,
            disableFlip: false,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };
    }

    function initScanner() {
        const loadingEl = document.getElementById('scanner-loading');
        if (typeof Html5Qrcode === 'undefined') {
            setTimeout(initScanner, 100);
            return;
        }

        html5QrCode = new Html5Qrcode("qr-reader");

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                cameraList = cameras;
                const backCam = cameras.find(c => {
                    const l = c.label.toLowerCase();
                    return l.includes('back') || l.includes('rear') || l.includes('environment');
                });
                const selectedCamera = backCam ? backCam.id : cameras[0].id;
                currentCameraIndex = cameras.findIndex(c => c.id === selectedCamera);

                startCamera(selectedCamera);
            } else {
                showCameraError('Tidak Ditemukan Kamera', 'Pilih lokasi parkir Anda secara langsung pada daftar di bawah.');
            }
        }).catch(err => {
            console.error("Camera enum error:", err);
            showCameraError('Akses Kamera Diperlukan', 'Izinkan akses kamera di browser Anda, atau pilih lokasi parkir pada daftar di bawah.');
        });
    }

    function startCamera(cameraId) {
        const loadingEl = document.getElementById('scanner-loading');
        html5QrCode.start(
            cameraId,
            getScannerConfig(),
            (decodedText) => onScanSuccess(decodedText),
            (errorMessage) => {}
        ).then(() => {
            if (loadingEl) loadingEl.classList.add('hidden');
            checkTorchSupport();
        }).catch(err => {
            console.error("Camera start error:", err);
            showCameraError('Kamera Tidak Dapat Dibuka', 'Pilih lokasi parkir Anda secara langsung pada daftar di bawah.');
        });
    }

    function showCameraError(title, msg) {
        const loadingEl = document.getElementById('scanner-loading');
        if (loadingEl) {
            loadingEl.innerHTML = `
                <div class="text-amber-400 font-bold mb-1">${title}</div>
                <p class="text-[11px] text-white/70">${msg}</p>
            `;
        }
    }

    function switchCamera() {
        if (!cameraList || cameraList.length <= 1 || !html5QrCode) return;
        currentCameraIndex = (currentCameraIndex + 1) % cameraList.length;
        const nextCamId = cameraList[currentCameraIndex].id;

        html5QrCode.stop().then(() => {
            startCamera(nextCamId);
        }).catch(e => console.error(e));
    }

    function checkTorchSupport() {
        try {
            if (html5QrCode && html5QrCode.getRunningTrackCapabilities) {
                const capabilities = html5QrCode.getRunningTrackCapabilities();
                if (capabilities && capabilities.torch) {
                    const torchBtn = document.getElementById('btn-torch');
                    if (torchBtn) torchBtn.classList.remove('hidden');
                }
            }
        } catch(e) {}
    }

    function toggleTorch() {
        if (!html5QrCode) return;
        isTorchOn = !isTorchOn;
        try {
            html5QrCode.applyVideoConstraints({
                advanced: [{ torch: isTorchOn }]
            }).then(() => {
                const lbl = document.getElementById('torch-label');
                if (lbl) lbl.textContent = isTorchOn ? 'Matikan Senter' : 'Senter';
            }).catch(e => console.error(e));
        } catch (e) {}
    }

    function scanImageFile(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        const scanner = new Html5Qrcode("qr-reader");

        scanner.scanFile(file, true)
            .then(decodedText => {
                onScanSuccess(decodedText);
            })
            .catch(err => {
                alert("QR Code tidak dapat terbaca dari foto tersebut. Silakan pastikan foto cukup terang & fokus, atau pilih lokasi pada daftar di bawah.");
            });
    }

    function goToCode() {
        const val = document.getElementById('manual-code-input').value.trim();
        if (!val) {
            alert('Masukkan kode gerbang terlebih dahulu.');
            return;
        }
        window.location.href = '{{ url("/in") }}/' + encodeURIComponent(val);
    }

    function filterTenants(query) {
        const q = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.tenant-item');
        let visibleCount = 0;

        items.forEach(el => {
            const name = el.getAttribute('data-tenant-name') || '';
            if (!q || name.includes(q)) {
                el.classList.remove('hidden');
                visibleCount++;
            } else {
                el.classList.add('hidden');
            }
        });

        const noMatch = document.getElementById('no-tenant-match');
        if (noMatch) {
            if (visibleCount === 0 && items.length > 0) {
                noMatch.classList.remove('hidden');
            } else {
                noMatch.classList.add('hidden');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScanner);
    } else {
        initScanner();
    }
</script>
@endsection
