@extends('layouts.app')
@section('title', 'Karcis Digital QR — ' . $transaksi->kode_karcis)

@section('content')
@php
    $isMember = $transaksi->metode_pembayaran === 'Member_Pass';
    $isBooking = $transaksi->metode_pembayaran === 'Prepaid_Booking';
    $isFastTrack = $isMember || $isBooking;
@endphp

<div class="min-h-screen bg-slate-900 text-white flex flex-col justify-between p-4 sm:p-6 print:bg-white print:p-0">
    <header class="max-w-md mx-auto w-full py-3 flex items-center justify-between print:hidden">
        <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-white/70 hover:text-white transition">
            &larr; Beranda
        </a>
        <div id="top-badge-container">
            @if($transaksi->status === 'keluar')
                <span class="text-xs font-mono font-bold text-slate-300 bg-slate-800 px-2.5 py-1 rounded-full border border-slate-600">SUDAH KELUAR</span>
            @elseif($isMember)
                <span class="text-xs font-mono font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-1 rounded-full border border-emerald-500/30">GREEN DIGITAL PASS</span>
            @elseif($isBooking)
                <span class="text-xs font-mono font-bold text-purple-300 bg-purple-950/80 px-2.5 py-1 rounded-full border border-purple-500/30">BOOKING INAP PASS</span>
            @else
                <span class="text-xs font-mono font-bold text-accent">KARCIS RESMI AKTIF</span>
            @endif
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center my-auto print:my-0 print:p-0">
        <div id="ticket-card" class="w-full max-w-sm bg-white text-dark rounded-3xl p-6 sm:p-7 shadow-2xl relative border {{ $isMember ? 'border-emerald-500/80 ring-4 ring-emerald-500/10' : ($isBooking ? 'border-purple-500/80 ring-4 ring-purple-500/10' : 'border-slate-200 ring-4 ring-primary/5') }} print:shadow-none print:border-none print:max-w-none print:w-full overflow-hidden">
            
            {{-- Top Branded Accent Strip --}}
            <div class="h-2 w-full {{ $isMember ? 'bg-emerald-600' : ($isBooking ? 'bg-purple-600' : 'bg-primary') }} absolute top-0 left-0 right-0"></div>

            {{-- Header Struk Resmi --}}
            <div class="text-center pb-4 pt-1 border-b border-dashed border-slate-300 relative">
                <div class="mb-2 flex items-center justify-center">
                    <img src="{{ asset('assets/logo/primary/Teks.svg') }}" alt="Parkirin" class="h-9 w-auto mx-auto object-contain">
                </div>
                <h1 class="font-heading font-black text-lg text-dark leading-tight">{{ $transaksi->tempatParkir->nama_tempat ?? 'Parkirin' }}</h1>
                <div class="mt-1 flex items-center justify-center gap-1.5">
                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-100 text-primary font-mono text-[11px] font-bold tracking-wider border border-slate-200">
                        ID: {{ $transaksi->kode_karcis }}
                    </span>
                </div>

                @if($transaksi->status === 'keluar')
                    <div id="ticket-status-pill" class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold border border-slate-300">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Selesai — Sudah Keluar</span>
                    </div>
                @elseif($isMember)
                    <div id="ticket-status-pill" class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Member Pass Aktif (Fast-Track)</span>
                    </div>
                @elseif($isBooking)
                    <div id="ticket-status-pill" class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-bold border border-purple-200">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Booking Inap (Prepaid Lunas)</span>
                    </div>
                @else
                    <div id="ticket-status-pill" class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold border border-primary/20">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        <span>Karcis Mandiri (Self Check-In)</span>
                    </div>
                @endif
            </div>

            {{-- QR Code Display --}}
            <div class="my-5 text-center">
                <div class="p-3.5 {{ $isMember ? 'bg-emerald-50 border-emerald-200' : ($isBooking ? 'bg-purple-50 border-purple-200' : 'bg-slate-50 border-slate-200') }} rounded-2xl border-2 inline-block shadow-inner relative">
                    <div id="ticket-qrcode-wrap" class="p-3 bg-white rounded-xl shadow-xs inline-flex items-center justify-center">
                        <div id="qr-target" class="w-[180px] h-[180px] bg-white flex items-center justify-center">
                            <div id="qr-loading-placeholder" class="text-xs text-slate-400 flex flex-col items-center justify-center gap-2 p-4 text-center">
                                <div class="w-7 h-7 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-[11px] font-medium">Membuat Barcode QR...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs font-mono font-black {{ $isMember ? 'text-emerald-700' : ($isBooking ? 'text-purple-700' : 'text-primary') }} tracking-widest uppercase">
                    {{ $transaksi->kode_karcis }}
                </p>
                <div id="gate-status-msg">
                    @if($transaksi->status === 'keluar')
                        <p class="text-[11px] text-slate-500 font-bold flex items-center justify-center gap-1.5 mt-1">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Karcis Sudah Selesai Digunakan</span>
                        </p>
                    @else
                        <p class="text-[11px] text-emerald-600 font-bold flex items-center justify-center gap-1.5 mt-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Gerbang Terbuka Otomatis</span>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Detail Transaksi Table --}}
            <div class="space-y-2.5 py-3 border-t border-b border-dashed border-slate-300 text-xs font-sans">
                <div class="flex justify-between items-center">
                    <span class="text-dark/60 font-medium">Nomor Plat</span>
                    <span class="inline-flex items-center justify-center px-3 py-1 bg-black text-white font-mono font-black text-xs tracking-widest rounded-md border border-slate-700 shadow-sm">
                        {{ $transaksi->kendaraan->plat_nomor ?? '-' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-dark/60 font-medium">Jenis Kendaraan</span>
                    <span class="font-bold text-dark inline-flex items-center gap-1">
                        @if(($transaksi->kendaraan->jenis_kendaraan ?? $transaksi->tarif->jenis_kendaraan) === 'Mobil')
                            🚗 Mobil
                        @elseif(($transaksi->kendaraan->jenis_kendaraan ?? $transaksi->tarif->jenis_kendaraan) === 'Motor')
                            🛵 Motor
                        @else
                            🚲 Sepeda
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-dark/60 font-medium">Waktu Masuk</span>
                    <span class="font-semibold text-dark">{{ \Carbon\Carbon::parse($transaksi->waktu_masuk)->format('d/m/Y H:i') }} WIB</span>
                </div>
                <div class="flex justify-between items-center text-emerald-700 font-semibold {{ $transaksi->waktu_keluar ? '' : 'hidden' }}" id="row-waktu-keluar">
                    <span class="text-dark/60 font-medium">Waktu Keluar</span>
                    <span id="text-waktu-keluar">{{ $transaksi->waktu_keluar ? \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('d/m/Y H:i') . ' WIB' : '-' }}</span>
                </div>

                @if($isMember)
                    <div class="flex justify-between items-center">
                        <span class="text-dark/60 font-medium">Paket Langganan</span>
                        <span class="font-semibold text-emerald-700">{{ $transaksi->langganan->paket->nama_paket ?? 'Member Pass' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-dark/60 font-medium">Biaya Gerbang</span>
                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">Rp 0 (Member Lunas)</span>
                    </div>
                @elseif($isBooking)
                    <div class="flex justify-between items-center">
                        <span class="text-dark/60 font-medium">Kode Booking</span>
                        <span class="font-mono font-bold text-purple-700">{{ $transaksi->booking->kode_booking ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-dark/60 font-medium">Status Tagihan</span>
                        <span class="font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200/60">Lunas di Muka (Prepaid)</span>
                    </div>
                @else
                    <div class="flex justify-between items-center">
                        <span class="text-dark/60 font-medium">Skema Tarif</span>
                        <span class="font-semibold text-primary">{{ $transaksi->tarif->tipe_tarif ?? 'Reguler' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-dark/60 font-medium">Tipe Masuk</span>
                        <span class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[10px]">Self Check-In (Walk-In)</span>
                    </div>
                @endif
            </div>

            {{-- Actions: Export PNG, PDF, Print --}}
            <div class="mt-5 space-y-2.5 text-center print:hidden">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 flex items-center justify-center gap-2">
                    <span class="h-px w-8 bg-slate-200"></span>
                    <span>Simpan & Ekspor Karcis</span>
                    <span class="h-px w-8 bg-slate-200"></span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    {{-- Download PNG --}}
                    <button type="button" id="btn-download-png" onclick="downloadTicketPNG()" class="w-full py-2.5 px-3 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-2 shadow-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span id="label-png">Unduh PNG</span>
                    </button>

                    {{-- Download PDF --}}
                    <button type="button" id="btn-download-pdf" onclick="downloadTicketPDF()" class="w-full py-2.5 px-3 bg-primary hover:bg-primary-dark text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-2 shadow-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-amber-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span id="label-pdf">Unduh PDF</span>
                    </button>
                </div>

                {{-- Print Button --}}
                <button type="button" onclick="window.print()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-2 active:scale-95 cursor-pointer border border-slate-200">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Karcis Fisik (Print)</span>
                </button>

                <p class="text-[10.5px] text-slate-500 pt-1 leading-relaxed">
                    Karcis tersimpan otomatis di perangkat ini. Tunjukkan barcode QR ke scanner gerbang saat keluar.
                </p>
            </div>

        </div>
    </main>

    {{-- ═══════════════════════════════════════════════════════════
        MODAL SUKSES CHECKOUT PENGENDARA (QRIS CELEBRATION MODAL)
    ═══════════════════════════════════════════════════════════ --}}
    <div id="rider-checkout-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-sm bg-white text-slate-900 rounded-3xl p-6 sm:p-7 text-center shadow-2xl space-y-4 relative overflow-hidden transition-all duration-300 transform scale-95 opacity-0" id="rider-modal-box">
            
            {{-- Top Accent Strip --}}
            <div class="h-2.5 w-full bg-gradient-to-r from-emerald-500 to-teal-400 absolute top-0 left-0 right-0"></div>

            {{-- QRIS Success Ripple & Spring Checkmark --}}
            <div class="relative flex items-center justify-center pt-3 pb-1">
                {{-- Sonar Wave 1 --}}
                <div class="absolute w-28 h-28 rounded-full bg-emerald-400/20 animate-ping pointer-events-none"></div>
                {{-- Sonar Wave 2 --}}
                <div class="absolute w-24 h-24 rounded-full bg-emerald-500/25 animate-pulse pointer-events-none"></div>
                
                {{-- Main Checkmark Circle with Pop/Scale Animation --}}
                <div class="relative w-20 h-20 rounded-full bg-gradient-to-tr from-emerald-600 to-emerald-400 text-white flex items-center justify-center shadow-xl shadow-emerald-500/30 qris-pop-bounce">
                    <svg class="w-11 h-11 drop-shadow" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black uppercase tracking-wider mb-1.5 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Checkout Berhasil</span>
                </div>
                <h3 class="font-heading font-black text-2xl text-slate-900">Gerbang Telah Terbuka!</h3>
                <p class="text-xs text-slate-500 mt-1">Terima kasih atas kunjungan Anda. Hati-hati di jalan dan selamat sampai tujuan!</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 inline-block w-full">
                <div class="text-[10.5px] text-slate-400 uppercase tracking-wider font-bold">Plat Nomor</div>
                <div class="inline-flex items-center justify-center px-4 py-1.5 bg-black text-white font-mono font-black text-sm tracking-widest rounded-lg border border-slate-700 shadow-sm mt-1" id="modal-rider-plat">
                    {{ $transaksi->kendaraan->plat_nomor ?? '-' }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs text-left">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 text-[10px] block font-medium">Total Tagihan</span>
                    <span class="font-heading font-black text-base text-emerald-600" id="modal-rider-total">
                        {{ (float)$transaksi->biaya_dasar <= 0 ? 'Rp 0 (Lunas)' : 'Rp ' . number_format((float)$transaksi->biaya_dasar, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 text-[10px] block font-medium">Waktu Keluar</span>
                    <span class="font-bold text-xs text-slate-800" id="modal-rider-waktu">-</span>
                </div>
            </div>

            <button type="button" onclick="closeRiderCheckoutModal()" class="w-full py-3.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-sm transition active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Tutup & Lihat Ringkasan Karcis</span>
            </button>
        </div>
    </div>

    <footer class="max-w-md mx-auto w-full py-3 text-center text-xs text-white/50 print:hidden">
        Simpan karcis sebelum menutup browser &bull; &copy; {{ date('Y') }} Parkirin
    </footer>
</div>

<style>
@keyframes qrisPop {
    0% { transform: scale(0.2) rotate(-15deg); opacity: 0; }
    60% { transform: scale(1.18) rotate(3deg); opacity: 1; }
    80% { transform: scale(0.95) rotate(-1deg); }
    100% { transform: scale(1) rotate(0deg); opacity: 1; }
}
.qris-pop-bounce {
    animation: qrisPop 0.55s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@media print {
    body { background-color: #ffffff !important; }
    .print\:hidden { display: none !important; }
    #ticket-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; margin: 0 auto !important; }
}
</style>

{{-- Offline Local Libraries with CDN Backups --}}
<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
<script>window.QRCode || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>')</script>

<script src="{{ asset('assets/js/html2canvas.min.js') }}"></script>
<script src="{{ asset('assets/js/jspdf.umd.min.js') }}"></script>
<script>(window.jspdf || window.jsPDF) || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"><\/script>')</script>

<script>
window.parkirinQrCanvas = null;

document.addEventListener('DOMContentLoaded', function() {
    const target = document.getElementById('qr-target');
    const kodeKarcis = "{{ $transaksi->kode_karcis }}";

    // 1. Generate QR Code on screen & preserve canvas reference for export
    function initQR() {
        if (typeof QRCode !== 'undefined' && target) {
            try {
                target.innerHTML = '';
                new QRCode(target, {
                    text: kodeKarcis,
                    width: 180,
                    height: 180,
                    colorDark: "#000000",
                    colorLight: "#FFFFFF",
                    correctLevel: QRCode.CorrectLevel.M
                });

                // QRCode library creates both a canvas and an img.
                // Keep only the canvas visible, never both, to prevent layout distortion or cutting off!
                setTimeout(() => {
                    const c = target.querySelector('canvas');
                    const img = target.querySelector('img');
                    if (c) {
                        c.style.display = 'block';
                        c.style.margin = '0 auto';
                        c.style.width = '180px';
                        c.style.height = '180px';
                        window.parkirinQrCanvas = c;
                        if (img) img.style.display = 'none'; // Critical: hide duplicate img
                    } else if (img) {
                        img.style.display = 'block';
                        img.style.margin = '0 auto';
                        img.style.width = '180px';
                        img.style.height = '180px';
                    }
                }, 40);
            } catch (e) {
                console.warn('QR Generation warning:', e);
            }
        } else {
            setTimeout(initQR, 100);
        }
    }
    initQR();

    // Helper untuk sinkronisasi tiket aktif di localStorage
    function syncActiveTickets(action, record) {
        try {
            let list = JSON.parse(localStorage.getItem('parkirin_active_tickets') || '[]');
            if (!Array.isArray(list)) list = [];

            if (action === 'save' && record) {
                list = list.filter(t => t.kode_karcis !== record.kode_karcis);
                list.unshift(record);
                localStorage.setItem('parkirin_active_tickets', JSON.stringify(list));
                localStorage.setItem('parkirin_active_ticket', JSON.stringify(record));
            } else if (action === 'remove' && record?.kode_karcis) {
                list = list.filter(t => t.kode_karcis !== record.kode_karcis);
                localStorage.setItem('parkirin_active_tickets', JSON.stringify(list));
                const single = JSON.parse(localStorage.getItem('parkirin_active_ticket') || 'null');
                if (single && single.kode_karcis === record.kode_karcis) {
                    localStorage.removeItem('parkirin_active_ticket');
                }
            }
        } catch (e) {
            console.warn('Gagal sinkronisasi tiket di localStorage:', e);
        }
    }

    // 2. Simpan atau bersihkan karcis dari localStorage browser user
    const isSudahKeluar = "{{ $transaksi->status }}" === "keluar";
    const currentTicketRecord = {
        kode_karcis: kodeKarcis,
        plat_nomor: "{{ $transaksi->kendaraan->plat_nomor ?? '' }}",
        nama_tempat: "{{ $transaksi->tempatParkir->nama_tempat ?? '' }}",
        waktu_masuk: "{{ $transaksi->waktu_masuk }}",
        metode: "{{ $transaksi->metode_pembayaran }}",
        url: window.location.href,
        saved_at: new Date().toISOString()
    };

    if (isSudahKeluar) {
        syncActiveTickets('remove', currentTicketRecord);
    } else {
        syncActiveTickets('save', currentTicketRecord);
    }
});

/**
 * Helper to obtain jsPDF constructor safely
 */
function getJsPdfConstructor() {
    if (typeof window.jspdf !== 'undefined' && window.jspdf.jsPDF) {
        return window.jspdf.jsPDF;
    }
    if (typeof window.jsPDF !== 'undefined') {
        return window.jsPDF;
    }
    return null;
}

/**
 * Rounded rectangle helper for 2D Canvas
 */
function roundRect(ctx, x, y, w, h, r, fill, stroke) {
    if (typeof r === 'number') r = {tl: r, tr: r, br: r, bl: r};
    ctx.beginPath();
    ctx.moveTo(x + r.tl, y);
    ctx.lineTo(x + w - r.tr, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r.tr);
    ctx.lineTo(x + w, y + h - r.br);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r.br, y + h);
    ctx.lineTo(x + r.bl, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r.bl);
    ctx.lineTo(x, y + r.tl);
    ctx.quadraticCurveTo(x, y, x + r.tl, y);
    ctx.closePath();
    if (fill) ctx.fill();
    if (stroke) ctx.stroke();
}

/**
 * Perforated ticket divider with circular side cutouts
 */
function drawPerforatedDivider(ctx, y) {
    ctx.fillStyle = '#0F172A'; // Match page slate background
    ctx.beginPath();
    ctx.arc(0, y, 18, 0, Math.PI * 2);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(800, y, 18, 0, Math.PI * 2);
    ctx.fill();

    ctx.strokeStyle = '#CBD5E1';
    ctx.lineWidth = 2;
    ctx.setLineDash([8, 8]);
    ctx.beginPath();
    ctx.moveTo(28, y);
    ctx.lineTo(772, y);
    ctx.stroke();
    ctx.setLineDash([]);
}

/**
 * Corner targeting lines for QR frame
 */
function drawScannerCorners(ctx, x, y, w, h, color) {
    ctx.strokeStyle = color;
    ctx.lineWidth = 3.5;
    const len = 22;

    // Top-Left
    ctx.beginPath();
    ctx.moveTo(x, y + len);
    ctx.lineTo(x, y);
    ctx.lineTo(x + len, y);
    ctx.stroke();

    // Top-Right
    ctx.beginPath();
    ctx.moveTo(x + w - len, y);
    ctx.lineTo(x + w, y);
    ctx.lineTo(x + w, y + len);
    ctx.stroke();

    // Bottom-Left
    ctx.beginPath();
    ctx.moveTo(x, y + h - len);
    ctx.lineTo(x, y + h);
    ctx.lineTo(x + len, y + h);
    ctx.stroke();

    // Bottom-Right
    ctx.beginPath();
    ctx.moveTo(x + w - len, y + h);
    ctx.lineTo(x + w, y + h);
    ctx.lineTo(x + w, y + h - len);
    ctx.stroke();
}

/**
 * DEDICATED HIGH-RESOLUTION PARKIRIN TICKET CANVAS ENGINE
 * Renders an official, branded 800x1160 digital pass with GUARANTEED visible QR code!
 */
function renderDedicatedTicketCanvas(callback) {
    const canvas = document.createElement('canvas');
    canvas.width = 800;
    canvas.height = 1160;
    const ctx = canvas.getContext('2d');

    // 1. Background Card
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, 800, 1160);

    // 2. Top Header Bar (Deep Navy #0D63A5 Gradient)
    const headerGrad = ctx.createLinearGradient(0, 0, 800, 146);
    headerGrad.addColorStop(0, '#0D63A5');
    headerGrad.addColorStop(1, '#0A4F84');
    ctx.fillStyle = headerGrad;
    ctx.fillRect(0, 0, 800, 146);

    // Header Logo Icon
    ctx.fillStyle = '#FFFFFF';
    roundRect(ctx, 45, 34, 40, 40, 10, true, false);
    ctx.fillStyle = '#0D63A5';
    ctx.font = '900 24px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('P', 65, 54);
    ctx.fillStyle = '#FFD717';
    ctx.beginPath();
    ctx.arc(76, 65, 4.5, 0, Math.PI * 2);
    ctx.fill();

    // Brand Name: PARKIRIN
    ctx.fillStyle = '#FFFFFF';
    ctx.font = '900 30px sans-serif';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    ctx.fillText('PARKIR', 96, 50);
    ctx.fillStyle = '#FFD717';
    ctx.fillText('IN', 96 + ctx.measureText('PARKIR').width, 50);

    // Subtitle
    ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
    ctx.font = '600 12px sans-serif';
    ctx.fillText('KARCIS PARKIR DIGITAL RESMI • SISTEM MANDIRI', 96, 73);

    // Pass Type Pill (Header Right)
    const isMember = {{ $isMember ? 'true' : 'false' }};
    const isBooking = {{ $isBooking ? 'true' : 'false' }};
    let passLabel = 'REGULAR DIGITAL PASS';
    let passBg = '#1E293B';
    let passColor = '#FFD717';
    if (isMember) {
        passLabel = '★ GREEN DIGITAL PASS (MEMBER)';
        passBg = '#059669';
        passColor = '#FFFFFF';
    } else if (isBooking) {
        passLabel = '★ BOOKING INAP PASS (LUNAS)';
        passBg = '#7C3AED';
        passColor = '#FFFFFF';
    }
    ctx.font = 'bold 11px monospace';
    const pillW = ctx.measureText(passLabel).width + 24;
    ctx.fillStyle = passBg;
    roundRect(ctx, 755 - pillW, 42, pillW, 26, 13, true, false);
    ctx.fillStyle = passColor;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(passLabel, 755 - pillW / 2, 55);

    // Golden accent border below header
    ctx.fillStyle = '#FFD717';
    ctx.fillRect(0, 142, 800, 4);

    // 3. Location & Ticket Code Section (y = 155 to 265)
    ctx.fillStyle = '#0F172A';
    ctx.font = '900 27px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    ctx.fillText("{{ addslashes($transaksi->tempatParkir->nama_tempat ?? 'Parkirin') }}", 400, 168);

    ctx.fillStyle = '#64748B';
    ctx.font = '500 13.5px sans-serif';
    ctx.fillText("Gerbang Masuk Mandiri (Self Check-In)", 400, 204);

    // Monospace Ticket ID Pill
    ctx.fillStyle = '#F1F5F9';
    roundRect(ctx, 280, 228, 240, 32, 8, true, false);
    ctx.strokeStyle = '#CBD5E1';
    ctx.lineWidth = 1;
    roundRect(ctx, 280, 228, 240, 32, 8, false, true);

    ctx.fillStyle = '#0D63A5';
    ctx.font = 'bold 15px monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText("ID: {{ $transaksi->kode_karcis }}", 400, 244);

    // 4. Perforated Divider 1 (y = 280)
    drawPerforatedDivider(ctx, 280);

    // 5. Hero QR Code Card (y = 300 to 670)
    const brandColor = isMember ? '#059669' : (isBooking ? '#7C3AED' : '#0D63A5');
    
    // QR Container Box with quiet zone
    ctx.fillStyle = '#FFFFFF';
    roundRect(ctx, 240, 295, 320, 320, 24, true, false);
    ctx.strokeStyle = '#E2E8F0';
    ctx.lineWidth = 1.5;
    roundRect(ctx, 240, 295, 320, 320, 24, false, true);

    // Scanner corner accents positioned clearly outside the QR code
    drawScannerCorners(ctx, 248, 303, 304, 304, brandColor);

    const finishDrawing = (qrSource) => {
        // Draw the QR Code image or canvas centered with 25px safe quiet zone
        if (qrSource) {
            ctx.drawImage(qrSource, 280, 335, 240, 240);
        } else {
            ctx.fillStyle = '#000000';
            ctx.fillRect(280, 335, 240, 240);
        }

        // Ticket code below QR
        ctx.fillStyle = brandColor;
        ctx.font = '900 24px monospace';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        ctx.fillText("{{ $transaksi->kode_karcis }}", 400, 615);

        ctx.fillStyle = '#16A34A';
        ctx.font = 'bold 13.5px sans-serif';
        ctx.fillText("✓ GERBANG TERBUKA OTOMATIS", 400, 646);

        ctx.fillStyle = '#64748B';
        ctx.font = '500 12px sans-serif';
        ctx.fillText("Tunjukkan QR ini ke pemindai gerbang saat hendak keluar", 400, 668);

        // 6. Perforated Divider 2 (y = 698)
        drawPerforatedDivider(ctx, 698);

        // 7. Details Table Card (y = 720 to 1040)
        ctx.fillStyle = '#F8FAFC';
        roundRect(ctx, 50, 720, 700, 315, 18, true, false);
        ctx.strokeStyle = '#E2E8F0';
        ctx.lineWidth = 1.5;
        roundRect(ctx, 50, 720, 700, 315, 18, false, true);

        const rows = [
            { label: 'Nomor Plat Kendaraan', value: "{{ $transaksi->kendaraan->plat_nomor ?? '-' }}", isPlate: true },
            { label: 'Jenis Kendaraan', value: "{{ $transaksi->kendaraan->jenis_kendaraan ?? '-' }}", isPlate: false },
            { label: 'Waktu Masuk', value: "{{ \Carbon\Carbon::parse($transaksi->waktu_masuk)->format('d/m/Y H:i') }} WIB", isPlate: false },
            { label: 'Skema Layanan', value: "{{ $isMember ? ($transaksi->langganan->paket->nama_paket ?? 'Member Pass') : ($isBooking ? 'Booking Slot Inap' : ($transaksi->tarif->tipe_tarif ?? 'Reguler Walk-In')) }}", isPlate: false },
            { label: 'Biaya / Status Masuk', value: "{{ $isMember ? 'Rp 0 (Member Lunas)' : ($isBooking ? 'Rp 0 (Prepaid Lunas)' : 'Rp ' . number_format((float)($transaksi->biaya_total > 0 ? $transaksi->biaya_total : $transaksi->biaya_dasar), 0, ',', '.')) }}", isPlate: false, isCost: true },
        ];

        let curY = 740;
        rows.forEach((r, idx) => {
            ctx.fillStyle = '#64748B';
            ctx.font = '600 14.5px sans-serif';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(r.label, 80, curY + 20);

            if (r.isPlate) {
                // Indonesian TNKB Plate Badge
                ctx.fillStyle = '#000000';
                roundRect(ctx, 470, curY + 2, 250, 36, 8, true, false);
                ctx.strokeStyle = '#475569';
                ctx.lineWidth = 1.5;
                roundRect(ctx, 470, curY + 2, 250, 36, 8, false, true);

                ctx.fillStyle = '#FFFFFF';
                ctx.font = 'bold 19px monospace';
                ctx.textAlign = 'center';
                ctx.fillText(r.value, 470 + 125, curY + 20);
            } else if (r.isCost) {
                ctx.fillStyle = brandColor;
                ctx.font = '900 17px sans-serif';
                ctx.textAlign = 'right';
                ctx.fillText(r.value, 720, curY + 20);
            } else {
                ctx.fillStyle = '#0F172A';
                ctx.font = 'bold 15px sans-serif';
                ctx.textAlign = 'right';
                ctx.fillText(r.value, 720, curY + 20);
            }

            if (idx < rows.length - 1) {
                ctx.strokeStyle = '#EDF2F7';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(80, curY + 50);
                ctx.lineTo(720, curY + 50);
                ctx.stroke();
            }

            curY += 58;
        });

        // 8. Security Verification Footer (y = 1055 to 1140)
        ctx.fillStyle = '#94A3B8';
        ctx.font = '500 12px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        ctx.fillText("Valid Digital Ticket • Diterbitkan oleh Sistem Resmi Parkirin", 400, 1065);

        ctx.font = '400 11px monospace';
        ctx.fillStyle = '#CBD5E1';
        ctx.fillText("SECURITY-TOKEN: {{ strtoupper(substr(md5($transaksi->kode_karcis . $transaksi->waktu_masuk), 0, 20)) }} • {{ date('d/m/Y') }}", 400, 1086);

        callback(canvas);
    };

    // Grab the QR Code source from page canvas or regenerate cleanly in-memory
    const existingCanvas = window.parkirinQrCanvas || document.querySelector('#qr-target canvas');
    const existingImg = document.querySelector('#qr-target img');

    if (existingCanvas && existingCanvas.width > 0) {
        finishDrawing(existingCanvas);
    } else if (existingImg && existingImg.src && existingImg.src.startsWith('data:')) {
        const img = new Image();
        img.onload = () => finishDrawing(img);
        img.onerror = () => finishDrawing(null);
        img.src = existingImg.src;
    } else {
        // Offscreen generation fallback
        const offDiv = document.createElement('div');
        offDiv.style.position = 'absolute';
        offDiv.style.left = '-9999px';
        document.body.appendChild(offDiv);
        new QRCode(offDiv, {
            text: "{{ $transaksi->kode_karcis }}",
            width: 240,
            height: 240,
            colorDark: "#000000",
            colorLight: "#FFFFFF",
            correctLevel: QRCode.CorrectLevel.M
        });
        setTimeout(() => {
            const offCanvas = offDiv.querySelector('canvas');
            const offImg = offDiv.querySelector('img');
            if (offCanvas) {
                finishDrawing(offCanvas);
            } else if (offImg && offImg.src) {
                const img = new Image();
                img.onload = () => finishDrawing(img);
                img.onerror = () => finishDrawing(null);
                img.src = offImg.src;
            } else {
                finishDrawing(null);
            }
            document.body.removeChild(offDiv);
        }, 50);
    }
}

/**
 * Capture ticket card and trigger PNG download
 */
function downloadTicketPNG() {
    const btn = document.getElementById('btn-download-png');
    const label = document.getElementById('label-png');
    const filename = "Karcis-{{ $transaksi->kode_karcis }}.png";

    const originalLabel = label.innerText;
    label.innerText = 'Membuat PNG...';
    btn.disabled = true;

    renderDedicatedTicketCanvas(function(canvas) {
        try {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (e) {
            console.error('PNG export error:', e);
            alert('Gagal mengunduh gambar PNG karcis.');
        } finally {
            label.innerText = originalLabel;
            btn.disabled = false;
        }
    });
}

/**
 * Capture ticket card and trigger PDF download (Never redirects to print dialog!)
 */
function downloadTicketPDF() {
    const btn = document.getElementById('btn-download-pdf');
    const label = document.getElementById('label-pdf');
    const filename = "Karcis-{{ $transaksi->kode_karcis }}.pdf";

    const originalLabel = label.innerText;
    label.innerText = 'Membuat PDF...';
    btn.disabled = true;

    const JsPDF = getJsPdfConstructor();
    if (!JsPDF) {
        alert('Modul PDF sedang dimuat, silakan coba dalam 2 detik.');
        label.innerText = originalLabel;
        btn.disabled = false;
        return;
    }

    renderDedicatedTicketCanvas(function(canvas) {
        try {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new JsPDF({
                orientation: 'p',
                unit: 'mm',
                format: 'a6' // A6 is 105 x 148 mm
            });

            const pageWidth = pdf.internal.pageSize.getWidth(); // 105 mm
            const pageHeight = pdf.internal.pageSize.getHeight(); // 148 mm

            // Fit nicely within A6 margins (6.5mm horizontal margin)
            const marginX = 6.5;
            const targetW = pageWidth - (marginX * 2); // 92 mm
            const targetH = (canvas.height * targetW) / canvas.width; // 133.4 mm
            const marginY = (pageHeight - targetH) / 2; // ~7.3 mm

            pdf.addImage(imgData, 'PNG', marginX, marginY, targetW, targetH);
            pdf.save(filename);
        } catch (e) {
            console.error('PDF export error:', e);
            alert('Gagal mengunduh file PDF karcis.');
        } finally {
            label.innerText = originalLabel;
            btn.disabled = false;
        }
    });
}

/**
 * Web Audio API QRIS Success Chime
 */
function playSuccessChime() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        const now = ctx.currentTime;

        // QRIS 4-note ascending chord: C5 (523Hz), E5 (659Hz), G5 (784Hz), C6 (1046Hz)
        const notes = [
            { freq: 523.25, time: 0.00, dur: 0.12, gain: 0.22 },
            { freq: 659.25, time: 0.09, dur: 0.14, gain: 0.28 },
            { freq: 783.99, time: 0.19, dur: 0.16, gain: 0.34 },
            { freq: 1046.50, time: 0.31, dur: 0.45, gain: 0.38 }
        ];

        notes.forEach(note => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(note.freq, now + note.time);
            gain.gain.setValueAtTime(0.001, now + note.time);
            gain.gain.linearRampToValueAtTime(note.gain, now + note.time + 0.015);
            gain.gain.exponentialRampToValueAtTime(0.001, now + note.time + note.dur);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now + note.time);
            osc.stop(now + note.time + note.dur);
        });

        if (navigator.vibrate) {
            navigator.vibrate([80, 50, 120]);
        }
    } catch (e) {
        console.warn('Audio chime warning:', e);
    }
}

/**
 * Trigger celebration modal when ticket checkout is confirmed by officer
 */
function showRiderCheckoutModal(data) {
    const modal = document.getElementById('rider-checkout-modal');
    const box = document.getElementById('rider-modal-box');
    if (!modal || !box) return;

    if (data) {
        if (data.plat_nomor) {
            const elPlat = document.getElementById('modal-rider-plat');
            if (elPlat) elPlat.innerText = data.plat_nomor;
        }
        if (data.total_biaya_formatted) {
            const elTotal = document.getElementById('modal-rider-total');
            if (elTotal) elTotal.innerText = data.total_biaya <= 0 ? 'Rp 0 (Lunas)' : data.total_biaya_formatted;
        }
        if (data.waktu_keluar) {
            const elWaktu = document.getElementById('modal-rider-waktu');
            if (elWaktu) elWaktu.innerText = data.waktu_keluar;
        }
    }

    playSuccessChime();

    modal.classList.remove('hidden');
    setTimeout(() => {
        box.classList.remove('scale-95', 'opacity-0');
        box.classList.add('scale-100', 'opacity-100');
    }, 20);

    updateTicketPageToKeluar(data);
}

function closeRiderCheckoutModal() {
    const modal = document.getElementById('rider-checkout-modal');
    const box = document.getElementById('rider-modal-box');
    if (box) {
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

function updateTicketPageToKeluar(data) {
    const topBadge = document.getElementById('top-badge-container');
    if (topBadge) {
        topBadge.innerHTML = '<span class="text-xs font-mono font-bold text-slate-300 bg-slate-800 px-2.5 py-1 rounded-full border border-slate-600">SUDAH KELUAR</span>';
    }

    const statusPill = document.getElementById('ticket-status-pill');
    if (statusPill) {
        statusPill.className = 'mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-300 text-xs font-bold';
        statusPill.innerHTML = '<svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Selesai — Sudah Keluar</span>';
    }

    const gateMsg = document.getElementById('gate-status-msg');
    if (gateMsg) {
        gateMsg.innerHTML = '<p class="text-[11px] text-slate-500 font-bold flex items-center justify-center gap-1.5 mt-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>Karcis Sudah Selesai Digunakan</span></p>';
    }

    const rowKeluar = document.getElementById('row-waktu-keluar');
    const textKeluar = document.getElementById('text-waktu-keluar');
    if (rowKeluar) {
        rowKeluar.classList.remove('hidden');
        if (textKeluar && data && data.waktu_keluar) {
            textKeluar.innerText = data.waktu_keluar + ' WIB';
        }
    }
}

// Real-Time Polling for Rider Phone Screen (every 2.5s while ticket is active)
(function initTicketPolling() {
    const isMasuk = "{{ $transaksi->status }}" === "masuk";
    if (!isMasuk) return;

    let pollInterval = null;
    const statusUrl = "{{ route('checkin.ticket.status', $transaksi->kode_karcis, false) }}";

    pollInterval = setInterval(async () => {
        try {
            const res = await fetch(statusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data && data.success && data.is_keluar) {
                clearInterval(pollInterval);
                pollInterval = null;
                try {
                    let list = JSON.parse(localStorage.getItem('parkirin_active_tickets') || '[]');
                    if (Array.isArray(list)) {
                        list = list.filter(t => t.kode_karcis !== kodeKarcis);
                        localStorage.setItem('parkirin_active_tickets', JSON.stringify(list));
                    }
                    localStorage.removeItem('parkirin_active_ticket');
                } catch(e) {}
                showRiderCheckoutModal(data);
            }
        } catch (e) {
            // Silently retry on next interval
        }
    }, 2500);

    window.addEventListener('beforeunload', () => {
        if (pollInterval) clearInterval(pollInterval);
    });
})();
</script>
@endsection
