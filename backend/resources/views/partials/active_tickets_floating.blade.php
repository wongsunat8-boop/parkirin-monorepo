{{-- ══════════════════════════════════════════════════════════════════════
    FLOATING ACTIVE TICKETS WIDGET (Akses Cepat Karcis Aktif di Perangkat Ini)
    Hanya tampil jika ada karcis berstatus 'masuk' (masih aktif) di browser ini.
══════════════════════════════════════════════════════════════════════ --}}
<div x-data="floatingActiveTickets()"
     x-init="init()"
     x-cloak
     x-show="activeTickets.length > 0"
     class="fixed bottom-5 right-5 z-40 select-none font-sans"
     style="display: none;">

    {{-- Popover Card (Daftar Karcis Aktif) --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         @click.outside="isOpen = false"
         class="absolute bottom-16 right-0 w-[calc(100vw-2.5rem)] max-w-sm sm:w-96 bg-white text-slate-900 rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col z-50">
        
        {{-- Header Popover --}}
        <div class="bg-gradient-to-r from-primary to-primary-dark text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-heading font-black text-sm text-white leading-tight">Karcis Parkir Aktif</h3>
                    <p class="text-[11px] text-white/80">Tersimpan di browser perangkat ini</p>
                </div>
            </div>
            <button type="button" @click="isOpen = false" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Ticket Items List --}}
        <div class="p-3.5 space-y-2.5 max-h-[60vh] overflow-y-auto">
            <template x-for="ticket in activeTickets" :key="ticket.kode_karcis">
                <div class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="badge-plat text-xs font-mono font-bold tracking-wider" x-text="ticket.plat_nomor"></span>
                            <div class="text-xs font-bold text-slate-800 mt-1" x-text="ticket.nama_tempat || 'Lahan Parkir Swadaya'"></div>
                            <div class="text-[11px] text-slate-500 font-mono" x-text="ticket.kode_karcis"></div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Sedang Parkir</span>
                        </span>
                    </div>

                    <div class="pt-1 flex items-center gap-2">
                        <a :href="ticket.url || ('{{ url('/in/karcis') }}/' + ticket.kode_karcis)"
                           class="flex-1 py-2 px-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-bold text-xs text-center transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Buka Karcis</span>
                        </a>
                        <button type="button"
                                @click="removeTicket(ticket.kode_karcis)"
                                title="Hapus dari daftar perangkat ini"
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Popover --}}
        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <span>Tunjukkan QR karcis ini ke petugas saat keluar.</span>
        </div>
    </div>

    {{-- Main Floating Trigger Button --}}
    <button type="button"
            @click="isOpen = !isOpen"
            class="relative inline-flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm shadow-2xl border border-white/20 transition-all transform hover:scale-105 active:scale-95 cursor-pointer ring-4 ring-black/5">
        
        {{-- Pulsing Green Indicator --}}
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>

        {{-- Ticket Icon --}}
        <svg class="w-4 h-4 text-accent shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
        </svg>

        {{-- Label --}}
        <span class="font-heading font-extrabold tracking-tight">Karcis Parkir</span>

        {{-- Badge Counter if > 1 --}}
        <template x-if="activeTickets.length > 1">
            <span class="px-1.5 py-0.5 rounded-full bg-accent text-slate-950 font-black text-[10px] leading-none" x-text="activeTickets.length"></span>
        </template>
    </button>
</div>

<script>
function floatingActiveTickets() {
    return {
        activeTickets: [],
        isOpen: false,
        loading: true,

        async init() {
            let saved = [];
            try {
                saved = JSON.parse(localStorage.getItem('parkirin_active_tickets') || '[]');
                if (!Array.isArray(saved)) saved = [];

                // Cek data legacy 'parkirin_active_ticket' untuk kompatibilitas
                const single = JSON.parse(localStorage.getItem('parkirin_active_ticket') || 'null');
                if (single && single.kode_karcis && !saved.some(t => t.kode_karcis === single.kode_karcis)) {
                    saved.unshift(single);
                }
            } catch(e) {
                saved = [];
            }

            if (!saved.length) {
                this.loading = false;
                return;
            }

            // Validasi ke server secara asynchronous: HANYA TIKET YANG MASIH AKTIF (status 'masuk')
            const verified = [];
            for (const t of saved) {
                if (!t || !t.kode_karcis) continue;
                try {
                    const res = await fetch(`{{ url('/in/karcis') }}/${encodeURIComponent(t.kode_karcis)}/status`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        // Hanya simpan jika karcis masih aktif (status = 'masuk', belum keluar)
                        if (data && data.success && data.status === 'masuk' && !data.is_keluar) {
                            t.plat_nomor = data.plat_nomor || t.plat_nomor;
                            t.nama_tempat = data.nama_tempat || t.nama_tempat;
                            verified.push(t);
                        }
                    } else if (res.status === 404) {
                        // Karcis tidak ada lagi di database, buang
                        continue;
                    } else {
                        // Respon server lain (misal status 500 sementara): tahan jika baru < 24 jam
                        verified.push(t);
                    }
                } catch(e) {
                    // Jika offline / gangguan jaringan sementara: simpan jika baru disimpan < 24 jam
                    const savedAt = t.saved_at ? new Date(t.saved_at).getTime() : 0;
                    if (Date.now() - savedAt < 24 * 3600 * 1000) {
                        verified.push(t);
                    }
                }
            }

            this.activeTickets = verified;
            try {
                localStorage.setItem('parkirin_active_tickets', JSON.stringify(verified));
                if (verified.length === 0) {
                    localStorage.removeItem('parkirin_active_ticket');
                }
            } catch(e) {}
            this.loading = false;
        },

        removeTicket(kodeKarcis) {
            this.activeTickets = this.activeTickets.filter(t => t.kode_karcis !== kodeKarcis);
            try {
                localStorage.setItem('parkirin_active_tickets', JSON.stringify(this.activeTickets));
                if (this.activeTickets.length === 0) {
                    localStorage.removeItem('parkirin_active_ticket');
                    this.isOpen = false;
                }
            } catch(e) {}
        }
    };
}
</script>
