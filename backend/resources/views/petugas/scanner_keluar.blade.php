@extends('layouts.app')
@section('title', 'Pemindai QR Karcis Keluar — Parkirin')

@section('content')
<div class="min-h-screen bg-slate-950 text-white flex flex-col justify-between font-sans relative overflow-hidden" x-data="exitScannerApp()" x-init="initScanner()">
    
    {{-- Background glowing accents (Same as Self Check-In) --}}
    <div class="absolute inset-0 pointer-events-none opacity-20">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-primary blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-accent blur-3xl"></div>
    </div>

    {{-- Top Navigation Bar --}}
    <header class="relative z-20 border-b border-white/10 bg-slate-950/80 backdrop-blur-md">
        <div class="max-w-2xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <a href="{{ route('petugas.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white transition py-1.5 px-3 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard Petugas</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-white/5 border border-white/10 text-white/80">
                    <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="truncate max-w-[140px]">{{ $tenant->nama_tempat ?? 'Lokasi Parkir' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 border border-white/15 text-accent font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Pemindai Karcis QR</span>
                </span>
            </div>
        </div>
    </header>

    {{-- Main Viewport --}}
    <main class="relative z-10 flex-1 flex flex-col items-center justify-start p-4 sm:p-6 max-w-lg mx-auto w-full space-y-5">
        
        {{-- Instruction Header --}}
        <div class="text-center pt-2">
            <h1 class="font-heading font-black text-2xl sm:text-3xl text-white tracking-tight">
                Scan Karcis Keluar
            </h1>
            <p class="text-sm sm:text-base text-white/80 mt-1.5 max-w-md mx-auto leading-relaxed">
                Arahkan kamera ke QR karcis di HP pengendara untuk cek tarif & buka gerbang.
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
                <div id="scanner-loading" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center text-sm text-white/90 space-y-3 z-30">
                    <div class="w-10 h-10 border-3 border-accent border-t-transparent rounded-full animate-spin"></div>
                    <p class="font-bold text-white text-base">Menghubungkan Kamera...</p>
                    <p class="text-xs text-white/70 max-w-xs">Izinkan akses kamera di browser HP Anda untuk memindai karcis secara instan.</p>
                </div>

                {{-- Success Flash Overlay --}}
                <div id="scanner-success" class="hidden absolute inset-0 bg-emerald-500/95 backdrop-blur-md flex-col items-center justify-center p-6 text-center z-40 transition-all duration-200">
                    <div class="w-16 h-16 rounded-full bg-white text-emerald-600 flex items-center justify-center mb-3 shadow-xl animate-bounce">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="font-heading font-black text-2xl text-white">QR Karcis Terbaca!</p>
                    <p class="text-sm text-white/90 mt-1 font-medium">Menghitung tagihan otomatis...</p>
                </div>

            </div>

            {{-- Scanning tips --}}
            <div class="mt-3 px-3.5 py-2.5 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-2.5 text-xs sm:text-sm text-white/80">
                <span class="text-accent text-base shrink-0">💡</span>
                <span class="leading-snug"><strong>Petunjuk:</strong> Arahkan kamera ke QR karcis di HP pengendara. Jaga jarak <strong>20–30 cm</strong>.</span>
            </div>

            {{-- Scanner Controls (Hanya Kontrol Kamera yang Relevan: Ganti Kamera & Senter) --}}
            <div class="mt-3 flex items-center gap-2">
                <button type="button" id="btn-switch-camera" onclick="switchCamera()" class="flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs sm:text-sm transition border border-white/10 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Ganti Kamera</span>
                </button>
                <button type="button" id="btn-torch" onclick="toggleTorch()" class="hidden flex-1 inline-flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs sm:text-sm transition border border-white/10 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span id="torch-label">Nyalakan Lampu</span>
                </button>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════
            FALLBACK: CARI PLAT NOMOR (KARCIS HILANG / HP MATI)
        ═══════════════════════════════════════════ --}}
        <div class="w-full bg-slate-900 rounded-3xl border border-white/15 p-4 sm:p-5 shadow-xl space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-black text-sm sm:text-base text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Karcis Hilang atau HP Pengendara Mati?</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-white/70 mt-0.5">Ketik plat nomor kendaraan untuk cek data masuk secara manual</p>
                </div>
            </div>

            <form @submit.prevent="lookupByPlat()" class="flex gap-2">
                <input type="text" x-model="searchPlat" placeholder="Contoh: B 1234 XYZ" class="w-full px-3.5 py-3 bg-slate-950 border border-white/20 rounded-2xl text-sm sm:text-base text-white placeholder:text-white/40 uppercase font-mono tracking-wider focus:border-accent focus:outline-none transition">
                <button type="submit" :disabled="searching" class="px-5 py-3 bg-accent hover:bg-accent-dark text-slate-950 font-black text-sm sm:text-base rounded-2xl transition cursor-pointer flex items-center gap-2 shrink-0 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span x-text="searching ? 'Mencari...' : 'Cari Plat Nomor'"></span>
                </button>
            </form>
        </div>

        {{-- Error Alert --}}
        <div x-show="errorMessage" x-transition x-cloak class="w-full p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-200 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <div class="flex-1">
                <p class="font-bold text-white text-xs">Pemberitahuan</p>
                <p class="mt-0.5" x-text="errorMessage"></p>
            </div>
            <button type="button" @click="errorMessage = ''" class="text-rose-400 hover:text-white">&times;</button>
        </div>

    </main>

    {{-- ═══════════════════════════════════════════
        MODAL RINCIAN TAGIHAN & CHECKOUT KASIR
    ═══════════════════════════════════════════ --}}
    <div x-show="scanResult" x-transition.opacity x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4">
        
        <div class="w-full max-w-lg bg-white text-slate-900 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col" @click.away="resetScan()">
            
            {{-- Top Branded Accent Strip --}}
            <div class="h-2.5 w-full" :class="scanResult?.is_member ? 'bg-emerald-600' : (scanResult?.is_booking ? 'bg-purple-600' : 'bg-primary')"></div>

            <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
                
                {{-- Modal Header: Plat Nomor & Status --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                             :class="scanResult?.is_member ? 'bg-emerald-50 text-emerald-600' : (scanResult?.is_booking ? 'bg-purple-50 text-purple-600' : 'bg-primary/10 text-primary')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        </div>
                        <div>
                            <span class="badge-plat text-base sm:text-lg font-mono font-black tracking-wider px-3.5 py-1" x-text="scanResult?.plat_nomor"></span>
                            <div class="text-xs sm:text-sm text-slate-600 font-semibold mt-1">
                                <span x-text="scanResult?.jenis_kendaraan"></span> • <span x-text="scanResult?.warna"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Pass Type Badge --}}
                    <div>
                        <template x-if="scanResult?.is_member">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span>★ Member Lunas</span>
                            </span>
                        </template>
                        <template x-if="scanResult?.is_booking">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                <span>★ Booking Lunas</span>
                            </span>
                        </template>
                        <template x-if="!scanResult?.is_member && !scanResult?.is_booking">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-primary border border-blue-200">
                                Karcis Reguler
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Ticket Details Table (Mobile-Friendly & Readable) --}}
                <div class="space-y-2.5 py-2 text-sm border-b border-dashed border-slate-200">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Nomor Karcis</span>
                        <span class="font-mono font-bold text-primary" x-text="scanResult?.kode_karcis"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Waktu Masuk</span>
                        <span class="font-bold text-slate-800" x-text="scanResult?.waktu_masuk"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Lama Parkir</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-primary border border-blue-200/60"
                              x-text="scanResult?.tipe_tarif === 'Flat Harian' ? (scanResult?.is_inap ? scanResult?.durasi + ' (' + scanResult?.jumlah_malam + ' Malam Inap)' : scanResult?.durasi + ' (Parkir Hari Ini)') : (scanResult?.durasi + ' (' + scanResult?.durasi_jam + ' jam)')"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Jenis Tarif</span>
                        <span class="font-bold text-slate-800" x-text="scanResult?.tipe_tarif"></span>
                    </div>
                    <template x-if="scanResult?.nama_paket">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Paket Langganan</span>
                            <span class="font-bold text-emerald-700" x-text="scanResult?.nama_paket"></span>
                        </div>
                    </template>
                    <template x-if="scanResult?.kode_booking">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Kode Booking</span>
                            <span class="font-mono font-bold text-purple-700" x-text="scanResult?.kode_booking"></span>
                        </div>
                    </template>
                </div>

                {{-- Price Breakdown --}}
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600" x-text="scanResult?.is_inap ? 'Biaya Inap (' + scanResult?.jumlah_malam + ' Malam)' : (scanResult?.tipe_tarif === 'Flat Harian' ? 'Biaya Parkir (Flat Harian)' : 'Biaya Parkir')"></span>
                        <span class="font-bold text-slate-800" x-text="formatRupiah(scanResult?.biaya_dasar)"></span>
                    </div>
                    <template x-if="scanResult?.denda_overstay > 0">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Denda Lewat Waktu</span>
                            <span class="font-bold text-amber-600" x-text="formatRupiah(scanResult?.denda_overstay)"></span>
                        </div>
                    </template>
                    <template x-if="scanResult?.is_lost_ticket">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">Denda Karcis Hilang</span>
                            <span class="font-bold text-rose-600" x-text="formatRupiah(scanResult?.denda_karcis_hilang)"></span>
                        </div>
                    </template>
                </div>

                {{-- Highlight Total Box (Besar, Kontras & Mudah Dibaca) --}}
                <div class="p-4 sm:p-5 rounded-2xl flex items-center justify-between border"
                     :class="scanResult?.total_biaya <= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-900'">
                    <div>
                        <div class="text-xs uppercase tracking-wider font-bold text-slate-500">Total Pembayaran</div>
                        <div class="text-xs sm:text-sm font-semibold mt-0.5"
                             :class="scanResult?.total_biaya <= 0 ? 'text-emerald-700' : 'text-slate-600'"
                             x-text="scanResult?.total_biaya <= 0 ? 'Gratis — Langsung buka gerbang' : 'Wajib diselesaikan di kasir'"></div>
                    </div>
                    <div class="font-heading font-black text-3xl sm:text-4xl tracking-tight"
                         :class="scanResult?.total_biaya <= 0 ? 'text-emerald-700' : 'text-primary'"
                         x-text="formatRupiah(scanResult?.total_biaya)"></div>
                </div>

                {{-- Input STNK/KTP (Khusus SOP Karcis Hilang) --}}
                <template x-if="scanResult?.is_lost_ticket">
                    <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-sm">
                        <label class="block font-bold text-amber-900 mb-1.5">Identitas Pengambil (No. STNK / KTP) *</label>
                        <input type="text" x-model="identitasPengambil" placeholder="Nomor STNK atau KTP pengendara" class="w-full px-3.5 py-2.5 bg-white border border-amber-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                    </div>
                </template>

                {{-- Metode Pembayaran (Tunai / QRIS) --}}
                <template x-if="scanResult?.total_biaya > 0">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-slate-800">Pilih Metode Pembayaran Kasir:</label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button" @click="metodeBayar = 'Tunai'"
                                class="py-3 px-4 rounded-2xl text-sm sm:text-base font-bold flex items-center justify-center gap-2 border-2 transition cursor-pointer"
                                :class="metodeBayar === 'Tunai' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                                <span class="text-lg">💵</span>
                                <span>Uang Tunai</span>
                            </button>
                            <button type="button" @click="metodeBayar = 'QRIS_Mitra'"
                                class="py-3 px-4 rounded-2xl text-sm sm:text-base font-bold flex items-center justify-center gap-2 border-2 transition cursor-pointer"
                                :class="metodeBayar === 'QRIS_Mitra' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                                <span class="text-lg">📱</span>
                                <span>QRIS Mitra</span>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Action Checkout Button --}}
                <div class="pt-2 space-y-2.5">
                    {{-- Modal Error Alert --}}
                    <div x-show="modalErrorMessage" x-transition x-cloak class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <div class="flex-1">
                            <p class="font-bold text-rose-800">Perhatian</p>
                            <p class="mt-0.5" x-text="modalErrorMessage"></p>
                        </div>
                        <button type="button" @click="modalErrorMessage = ''" class="text-rose-400 hover:text-rose-700 text-base font-bold">&times;</button>
                    </div>

                    <button type="button" @click="processCheckout()" :disabled="processing"
                        class="w-full py-4 px-5 rounded-2xl font-black text-base sm:text-lg transition active:scale-[0.98] flex items-center justify-center gap-2.5 shadow-lg disabled:opacity-50 cursor-pointer text-white"
                        :class="scanResult?.total_biaya <= 0 ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-primary hover:bg-primary-dark'">
                        <svg x-show="!processing" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="processing" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="processing ? 'Memproses Keluar...' : (scanResult?.total_biaya <= 0 ? 'Selesai — Buka Gerbang' : 'Terima Uang & Buka Gerbang')"></span>
                    </button>

                    <button type="button" @click="resetScan()" class="w-full py-3 text-sm sm:text-base font-bold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                        Batal — Scan Karcis Lain
                    </button>
                </div>

            </div>

        </div>

    </div>

    {{-- ═══════════════════════════════════════════
        MODAL SUKSES CHECKOUT
    ═══════════════════════════════════════════ --}}
    <div x-show="checkoutReceipt" x-transition.opacity x-cloak class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white text-slate-900 rounded-3xl p-6 sm:p-7 text-center shadow-2xl space-y-4 relative overflow-hidden"
             x-show="checkoutReceipt"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            {{-- Top Accent Strip --}}
            <div class="h-2.5 w-full bg-emerald-500 absolute top-0 left-0 right-0"></div>

            {{-- QRIS Success Ripple & Spring Checkmark --}}
            <div class="relative flex items-center justify-center pt-3 pb-1">
                <div class="absolute w-28 h-28 rounded-full bg-emerald-400/20 animate-ping pointer-events-none"></div>
                <div class="absolute w-24 h-24 rounded-full bg-emerald-500/25 animate-pulse pointer-events-none"></div>
                
                <div class="relative w-20 h-20 rounded-full bg-gradient-to-tr from-emerald-600 to-emerald-400 text-white flex items-center justify-center shadow-xl shadow-emerald-500/30 qris-pop-bounce">
                    <svg class="w-11 h-11 drop-shadow" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            
            <div>
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider mb-2 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Transaksi Berhasil</span>
                </div>
                <h3 class="font-heading font-black text-2xl sm:text-3xl text-slate-900">Gerbang Keluar Terbuka!</h3>
                <p class="text-sm text-slate-600 mt-1" x-text="checkoutReceipt?.message"></p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 inline-block w-full">
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Plat Nomor</div>
                <div class="badge-plat text-lg sm:text-xl mt-1.5 font-mono font-black" x-text="checkoutReceipt?.plat_nomor"></div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 text-left">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500 text-xs block font-semibold">Total Pembayaran</span>
                    <span class="font-heading font-black text-lg sm:text-xl text-emerald-600" x-text="formatRupiah(checkoutReceipt?.total_biaya)"></span>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500 text-xs block font-semibold">Waktu Keluar</span>
                    <span class="font-bold text-sm text-slate-800" x-text="checkoutReceipt?.waktu_keluar"></span>
                </div>
            </div>

            <button type="button" @click="resetAll()" class="w-full py-4 rounded-2xl bg-slate-900 hover:bg-black text-white font-black text-sm sm:text-base shadow-lg transition active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h3v3h-3v-3zM13.5 19.5h3v.75h-3v-.75zM19.5 13.5h.75v3h-.75v-3zM19.5 19.5h.75v.75h-.75v-.75z"/></svg>
                <span>Siap Scan Kendaraan Berikutnya</span>
            </button>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="relative z-20 py-4 text-center text-xs text-white/40 font-sans border-t border-white/5">
        &copy; {{ date('Y') }} Parkirin &bull; Sistem Pemindai Karcis Digital Petugas
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
    @keyframes qrisPop {
        0% { transform: scale(0.2) rotate(-15deg); opacity: 0; }
        60% { transform: scale(1.18) rotate(3deg); opacity: 1; }
        80% { transform: scale(0.95) rotate(-1deg); }
        100% { transform: scale(1) rotate(0deg); opacity: 1; }
    }
    .qris-pop-bounce {
        animation: qrisPop 0.55s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    #qr-reader {
        border: none !important;
        width: 100% !important;
        height: 100% !important;
    }
    #qr-reader video {
        border-radius: 1rem !important;
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
    }
    #qr-reader__scan_region {
        min-height: 240px;
    }
    #qr-reader__dashboard_section_csr button {
        display: none !important;
    }
    #qr-reader__header_message,
    #qr-reader__dashboard_section_swaplink,
    #qr-reader__filescan_input {
        display: none !important;
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

{{-- html5-qrcode library with fallback --}}
<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script>window.Html5Qrcode || document.write('<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>')</script>

<script>
let globalHtml5QrCode = null;
let cameraList = [];
let currentCameraIndex = 0;
let isTorchOn = false;
let isProcessing = false;

function playBeep() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const now = ctx.currentTime;

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

function playSuccessChime() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }
        const now = ctx.currentTime;

        // QRIS/E-Wallet harmonic success chime: C5 (523Hz), E5 (659Hz), G5 (784Hz), C6 (1046Hz)
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

function startCamera(cameraId) {
    const loadingEl = document.getElementById('scanner-loading');
    if (!globalHtml5QrCode) return;

    globalHtml5QrCode.start(
        cameraId,
        getScannerConfig(),
        (decodedText) => {
            window.dispatchEvent(new CustomEvent('qr-scanned', { detail: decodedText }));
        },
        (errorMessage) => {}
    ).then(() => {
        if (loadingEl) loadingEl.classList.add('hidden');
        checkTorchSupport();
    }).catch(err => {
        console.error("Camera start error:", err);
        showCameraError('Kamera Tidak Dapat Dibuka', 'Pastikan izin akses kamera sudah diberikan pada browser.');
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
    if (!cameraList || cameraList.length <= 1 || !globalHtml5QrCode) return;
    currentCameraIndex = (currentCameraIndex + 1) % cameraList.length;
    const nextCamId = cameraList[currentCameraIndex].id;

    globalHtml5QrCode.stop().then(() => {
        startCamera(nextCamId);
    }).catch(e => console.error(e));
}

function checkTorchSupport() {
    try {
        if (globalHtml5QrCode && globalHtml5QrCode.getRunningTrackCapabilities) {
            const capabilities = globalHtml5QrCode.getRunningTrackCapabilities();
            if (capabilities && capabilities.torch) {
                const torchBtn = document.getElementById('btn-torch');
                if (torchBtn) torchBtn.classList.remove('hidden');
            }
        }
    } catch(e) {}
}

function toggleTorch() {
    if (!globalHtml5QrCode) return;
    isTorchOn = !isTorchOn;
    try {
        globalHtml5QrCode.applyVideoConstraints({
            advanced: [{ torch: isTorchOn }]
        }).then(() => {
            const lbl = document.getElementById('torch-label');
            if (lbl) lbl.textContent = isTorchOn ? 'Matikan Senter' : 'Nyalakan Lampu';
        }).catch(e => console.error(e));
    } catch (e) {}
}

function exitScannerApp() {
    return {
        scanResult: null,
        checkoutReceipt: null,
        errorMessage: '',
        modalErrorMessage: '',
        searchPlat: '',
        searching: false,
        processing: false,
        metodeBayar: 'Tunai',
        identitasPengambil: '',

        initScanner() {
            window.addEventListener('qr-scanned', (e) => {
                this.onQrDetected(e.detail);
            });

            this.setupHtml5Qr();
        },

        setupHtml5Qr() {
            if (typeof Html5Qrcode === 'undefined') {
                setTimeout(() => this.setupHtml5Qr(), 100);
                return;
            }

            globalHtml5QrCode = new Html5Qrcode("qr-reader");

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
                    showCameraError('Tidak Ditemukan Kamera', 'Gunakan fitur pencarian plat nomor di bawah.');
                }
            }).catch(err => {
                console.error("Camera enum error:", err);
                showCameraError('Akses Kamera Diperlukan', 'Izinkan browser menggunakan kamera untuk memindai karcis.');
            });
        },

        async onQrDetected(decodedText) {
            if (isProcessing) return;
            isProcessing = true;
            playBeep();

            // Pause camera while processing to prevent duplicate events
            if (globalHtml5QrCode && globalHtml5QrCode.isScanning) {
                try {
                    globalHtml5QrCode.pause(true);
                } catch(e) {}
            }

            const successOverlay = document.getElementById('scanner-success');
            if (successOverlay) {
                successOverlay.classList.remove('hidden');
                successOverlay.classList.add('flex');
            }

            // Extract ticket code from raw URL or plain text
            let code = (decodedText || '').trim();
            if (code.includes('/karcis/')) {
                code = code.split('/karcis/').pop().split('?')[0].split('/')[0].trim();
            } else if (code.includes('/ticket/')) {
                code = code.split('/ticket/').pop().split('?')[0].split('/')[0].trim();
            } else if (code.startsWith('http://') || code.startsWith('https://')) {
                code = code.split('/').pop().split('?')[0].trim();
            }

            setTimeout(async () => {
                if (successOverlay) {
                    successOverlay.classList.add('hidden');
                    successOverlay.classList.remove('flex');
                }
                await this.lookupKarcis(code);
            }, 500);
        },

        async lookupKarcis(kodeKarcis) {
            this.errorMessage = '';
            this.modalErrorMessage = '';
            this.scanResult = null;
            this.searching = true;

            const url = "{{ route('petugas.scanner-keluar.lookup', [], false) }}";

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ kode_karcis: kodeKarcis }),
                });

                const text = await response.text();
                let data = null;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response:', text);
                    this.errorMessage = 'Server mengembalikan respons tidak terduga (' + response.status + ').';
                    this.resumeCamera();
                    return;
                }

                if (!response.ok || !data || !data.success) {
                    this.errorMessage = (data && data.message) ? data.message : ('Karcis tidak ditemukan (' + response.status + ').');
                    this.resumeCamera();
                    return;
                }

                this.scanResult = data.transaksi;
                this.metodeBayar = (data.transaksi.is_member || data.transaksi.is_booking) ? data.transaksi.metode_pembayaran : 'Tunai';
            } catch (err) {
                console.error('Lookup error:', err);
                this.errorMessage = err.message ? ('Gagal menghubungi server: ' + err.message) : 'Terjadi gangguan jaringan saat memproses data karcis.';
                this.resumeCamera();
            } finally {
                this.searching = false;
                isProcessing = false;
            }
        },

        async lookupByPlat() {
            if (!this.searchPlat.trim()) return;

            this.errorMessage = '';
            this.modalErrorMessage = '';
            this.scanResult = null;
            this.searching = true;

            const url = "{{ route('petugas.scanner-keluar.search-plat', [], false) }}";

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ plat_nomor: this.searchPlat.trim() }),
                });

                const text = await response.text();
                let data = null;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response:', text);
                    this.errorMessage = 'Server mengembalikan respons tidak terduga (' + response.status + ').';
                    return;
                }

                if (!response.ok || !data || !data.success) {
                    this.errorMessage = (data && data.message) ? data.message : 'Kendaraan tidak ditemukan di area parkir saat ini.';
                    return;
                }

                this.scanResult = data.transaksi;
                this.metodeBayar = 'Tunai';

                // Pause camera while reviewing result
                if (globalHtml5QrCode && globalHtml5QrCode.isScanning) {
                    try { globalHtml5QrCode.pause(true); } catch(e) {}
                }
            } catch (err) {
                console.error('Search plat error:', err);
                this.errorMessage = err.message ? ('Gagal mencari: ' + err.message) : 'Terjadi gangguan jaringan saat mencari plat nomor.';
            } finally {
                this.searching = false;
            }
        },

        async processCheckout() {
            if (!this.scanResult) return;
            if (this.scanResult.is_lost_ticket && !this.identitasPengambil.trim()) {
                this.modalErrorMessage = 'Mohon isi identitas pengambil (Nomor STNK / KTP) untuk prosedur karcis hilang.';
                return;
            }

            this.processing = true;
            this.errorMessage = '';
            this.modalErrorMessage = '';

            const url = "{{ route('petugas.scanner-keluar.checkout', [], false) }}";

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        id_transaksi: this.scanResult.id_transaksi,
                        metode_bayar: this.metodeBayar,
                        denda_karcis_hilang: this.scanResult.is_lost_ticket ? this.scanResult.denda_karcis_hilang : 0,
                        identitas_pengambil: this.identitasPengambil || null,
                    }),
                });

                const text = await response.text();
                let data = null;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response:', text);
                    const msg = 'Server mengembalikan respons tidak terduga (' + response.status + ').';
                    this.modalErrorMessage = msg;
                    this.errorMessage = msg;
                    return;
                }

                if (!response.ok || !data || !data.success) {
                    let msg = (data && data.message) ? data.message : ('Gagal memproses pembayaran keluar (' + response.status + ').');
                    if (data && data.errors) {
                        msg += ' ' + Object.values(data.errors).flat().join(', ');
                    }
                    this.modalErrorMessage = msg;
                    this.errorMessage = msg;
                    return;
                }

                playSuccessChime();

                this.scanResult = null;
                this.modalErrorMessage = '';
                this.checkoutReceipt = {
                    message: data.message,
                    plat_nomor: data.data.plat_nomor,
                    total_biaya: data.data.total_biaya,
                    waktu_keluar: data.data.waktu_keluar,
                };
            } catch (err) {
                console.error('Checkout error:', err);
                const msg = err.message ? ('Gagal checkout: ' + err.message) : 'Gagal menghubungi server. Periksa koneksi internet.';
                this.modalErrorMessage = msg;
                this.errorMessage = msg;
            } finally {
                this.processing = false;
            }
        },

        resumeCamera() {
            isProcessing = false;
            if (globalHtml5QrCode) {
                try {
                    globalHtml5QrCode.resume();
                } catch(e) {}
            }
        },

        resetScan() {
            this.scanResult = null;
            this.identitasPengambil = '';
            this.metodeBayar = 'Tunai';
            this.errorMessage = '';
            this.modalErrorMessage = '';
            this.resumeCamera();
        },

        resetAll() {
            this.scanResult = null;
            this.checkoutReceipt = null;
            this.errorMessage = '';
            this.modalErrorMessage = '';
            this.searchPlat = '';
            this.identitasPengambil = '';
            this.metodeBayar = 'Tunai';
            this.resumeCamera();
        },

        formatRupiah(value) {
            if (value === 0 || value === '0' || value === null || !value) return 'Rp 0';
            const num = parseFloat(value);
            return 'Rp ' + num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    };
}
</script>
@endsection
