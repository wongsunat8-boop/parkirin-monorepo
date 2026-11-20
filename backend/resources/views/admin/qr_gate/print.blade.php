<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Gerbang Masuk — {{ $tenant->nama_tempat }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Nunito:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0D63A5',
                        accent: '#FFD717',
                        dark: '#0F172A',
                    },
                    fontFamily: {
                        heading: ['Nunito', 'sans-serif'],
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center font-sans text-dark">

    {{-- Control Bar (Screen only) --}}
    <div class="no-print max-w-lg w-full mb-6 flex items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <a href="{{ route('admin.qr-gate.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary">
            &larr; Kembali ke Panel Admin
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-blue-800 text-white font-bold text-sm rounded-xl shadow transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Standee Ini</span>
        </button>
    </div>

    {{-- Standee Sheet (A4 / A5 Printable Layout) --}}
    <div class="w-full max-w-md bg-white border-4 border-primary rounded-3xl p-8 sm:p-10 shadow-2xl text-center relative overflow-hidden">
        
        {{-- Header Accent Bar --}}
        <div class="absolute top-0 left-0 right-0 h-3 bg-gradient-to-r from-primary via-accent to-primary"></div>

        {{-- Brand Logo & Tagline --}}
        <div class="pt-2 mb-4">
            <span class="inline-block px-3 py-1 bg-accent/20 text-slate-900 border border-amber-300 rounded-full font-mono text-[11px] font-black uppercase tracking-widest mb-3">
                Pos Masuk Mandiri • Self Check-In
            </span>
            <h1 class="font-heading font-black text-2xl sm:text-3xl text-dark leading-tight">
                {{ $tenant->nama_tempat }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Pindai QR untuk Mendapatkan Karcis Digital</p>
        </div>

        {{-- High-Res QR Canvas Container --}}
        <div class="my-6 p-5 bg-white border-2 border-slate-900 rounded-3xl shadow-sm inline-block min-w-[280px]">
            <div id="qrcode" class="mx-auto flex items-center justify-center"></div>
            <img id="qr-fallback" 
                 src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($checkInUrl) }}" 
                 alt="QR Code" 
                 class="w-[260px] h-[260px] rounded-xl hidden mx-auto"
                 onload="if(!document.querySelector('#qrcode canvas')) { this.classList.remove('hidden'); }" />
            <p class="font-mono text-xs font-black text-slate-800 tracking-wider mt-3">{{ $tenant->kode_qr_gate }}</p>
        </div>

        {{-- 3-Step Driver Instructions --}}
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-left space-y-2 text-xs">
            <p class="font-heading font-bold text-slate-900 uppercase tracking-wider text-[11px] text-center mb-1">
                3 Langkah Cepat Masuk Parkir
            </p>
            <div class="flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
                <span class="text-slate-700">Buka <strong>Kamera HP</strong> atau menu <strong>Scan QR</strong> di website Parkirin.</span>
            </div>
            <div class="flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
                <span class="text-slate-700">Arahkan kamera ke QR Code di atas.</span>
            </div>
            <div class="flex items-start gap-2.5">
                <span class="w-5 h-5 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center shrink-0">3</span>
                <span class="text-slate-700">Ketik nomor plat & karcis digital QR Anda terbit seketika!</span>
            </div>
        </div>

        {{-- Footer Guarantee --}}
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-mono">
            <span>parkirin.id</span>
            <span>Gate System Verified</span>
        </div>

    </div>

    <script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
    <script>
        function renderPrintQr() {
            const target = document.getElementById('qrcode');
            const fallback = document.getElementById('qr-fallback');
            if (!target) return;

            try {
                if (typeof QRCode !== 'undefined') {
                    target.innerHTML = '';
                    new QRCode(target, {
                        text: "{{ $checkInUrl }}",
                        width: 260,
                        height: 260,
                        colorDark: "#0F172A",
                        colorLight: "#FFFFFF",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } else if (fallback) {
                    fallback.classList.remove('hidden');
                }
            } catch (e) {
                console.error(e);
                if (fallback) fallback.classList.remove('hidden');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderPrintQr);
        } else {
            renderPrintQr();
        }
    </script>
</body>
</html>
