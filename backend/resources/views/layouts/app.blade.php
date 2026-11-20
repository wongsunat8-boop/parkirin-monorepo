<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parkirin — Smart Parking SaaS')</title>

    {{-- Official Favicon SVG (Icon only) --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo/primary/Icon.svg') }}">

    {{-- Google Fonts: Nunito (Heading) & Inter (Sans/Body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Alpine.js for lightweight UI state reactivity --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    {{-- Tailwind CSS Config (Parkirin Palette & Typography) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0D63A5',
                            dark: '#0A4F84',
                            light: '#1A7DC8',
                        },
                        secondary: '#F9F9F9',
                        accent: {
                            DEFAULT: '#FFD717',
                            dark: '#E6C200',
                        },
                        dark: '#212121',
                        success: {
                            DEFAULT: '#16A34A',
                            bg: '#DCFCE7',
                        },
                        warning: {
                            DEFAULT: '#D97706',
                            bg: '#FEF3C7',
                        },
                        danger: {
                            DEFAULT: '#DC2626',
                            bg: '#FEE2E2',
                        },
                    },
                    fontFamily: {
                        heading: ['Nunito', 'sans-serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-50%)' },
                        },
                        'marquee-reverse': {
                            '0%': { transform: 'translateX(-50%)' },
                            '100%': { transform: 'translateX(0%)' },
                        },
                    },
                    animation: {
                        marquee: 'marquee 30s linear infinite',
                        'marquee-reverse': 'marquee-reverse 30s linear infinite',
                    },
                },
            },
        }
    </script>

    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F9F9F9;
            color: #212121;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Nunito', sans-serif;
        }
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        @keyframes marquee-reverse {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(0%); }
        }
        .animate-marquee {
            animation: marquee 30s linear infinite;
        }
        .animate-marquee-reverse {
            animation: marquee-reverse 30s linear infinite;
        }
        .animate-marquee:hover,
        .animate-marquee-reverse:hover {
            animation-play-state: paused;
        }
        .badge-plat {
            font-family: 'Inter', monospace;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background-color: #212121;
            color: #FFFFFF;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            border: 1px solid #404040;
            display: inline-flex;
            align-items: center;
        }
    </style>

    {{-- Chart.js for data visualization across dashboards --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-secondary text-dark antialiased">
    @yield('content')

    {{-- Akses Cepat Karcis Aktif di Browser (Floating) --}}
    @if(!request()->routeIs('petugas.*', 'admin.*', 'super-admin.*', 'superadmin.*', 'owner.*', 'pengendara.*', 'login', 'register', 'auth.*', 'checkin.ticket'))
        @include('partials.active_tickets_floating')
    @endif

    @stack('scripts')

    {{-- Global Currency (Ribuan) Input Formatter & Double-Submission Prevention --}}
    <script>
    (function() {
        // Format angka dengan pemisah ribuan titik
        function formatRibuanString(val) {
            if (val === null || val === undefined || val === '') return '';
            var str = val.toString().replace(/\D/g, '');
            if (!str) return '';
            return str.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function initRibuanInputs() {
            var inputs = document.querySelectorAll('.input-ribuan');
            inputs.forEach(function(input) {
                if (input._ribuanBound) return;
                input._ribuanBound = true;

                // Format nilai awal bila ada
                if (input.value) {
                    input.value = formatRibuanString(input.value);
                }

                input.addEventListener('input', function() {
                    var cursorPosition = input.selectionStart || 0;
                    var originalLength = input.value.length;
                    var raw = input.value.replace(/\D/g, '');
                    if (!raw) {
                        input.value = '';
                        return;
                    }
                    var formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    input.value = formatted;

                    var diff = formatted.length - originalLength;
                    var newCursor = Math.max(0, cursorPosition + diff);
                    try {
                        input.setSelectionRange(newCursor, newCursor);
                    } catch(e) {}
                });
            });
        }

        // Global Anti Double Submit & Input Ribuan Sanitizer on Submit
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // Bersihkan tanda titik pada semua input-ribuan sebelum payload dikirim
            form.querySelectorAll('.input-ribuan').forEach(function(input) {
                input.value = input.value.replace(/\D/g, '');
            });

            // Cek validasi form bawaan browser
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }

            // Cegah klik / enter ganda (double submit)
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            form.dataset.submitting = 'true';

            // Kunci tombol submit agar tidak diklik dua kali
            var submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(function(btn) {
                btn.classList.add('opacity-70', 'pointer-events-none', 'cursor-not-allowed');
                setTimeout(function() {
                    btn.disabled = true;
                }, 30);
            });
        }, true);

        // Reset submit lock & format ulang bila user kembali lewat tombol Back (BFCache / pageshow)
        window.addEventListener('pageshow', function() {
            document.querySelectorAll('form[data-submitting="true"]').forEach(function(form) {
                delete form.dataset.submitting;
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(btn) {
                    btn.classList.remove('opacity-70', 'pointer-events-none', 'cursor-not-allowed');
                    btn.disabled = false;
                });
            });
            initRibuanInputs();
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRibuanInputs);
        } else {
            initRibuanInputs();
        }
    })();
    </script>
</body>
</html>
