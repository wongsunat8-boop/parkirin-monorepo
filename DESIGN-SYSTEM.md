---
version: 2.0
name: Parkirin Enterprise (Broadcom-Inspired)
description: Design system tokens and implementation guidance for Parkirin Web/PWA. Combines Broadcom's light, editorial, enterprise-oriented architecture (sharp rectangular buttons, flat bordered cards, dark high-contrast hero, generous whitespace) with Parkirin's brand identity (#0D63A5, #FFD717, #F9F9F9, #212121, Nunito + Inter).
colors:
  primary: "#0D63A5"        # Parkirin Blue (Security & Trust)
  primary-dark: "#083D68"   # Deep Blue for dark editorial hero gradient/surface
  secondary: "#494A5E"      # Muted Slate (Calm enterprise body copy & utility UI)
  tertiary: "#E5E7EB"       # Crisp light neutral borders
  accent: "#FFD717"         # Traffic Yellow (High-visibility primary CTA & active markers)
  neutral:
    0: "#FFFFFF"            # Pure Surface White
    50: "#F9F9F9"           # Parkirin Soft Off-White (Alternating content bands)
    900: "#212121"          # Charcoal Black (Headings, dark hero/footer surfaces)
  surface:
    default: "#FFFFFF"
    subtle: "#F9F9F9"
  on-surface:
    heading: "#212121"
    body: "#494A5E"
  background:
    default: "#FFFFFF"
  semantic:
    success-text: "#16A34A"
    success-bg: "#DCFCE7"
    warning-text: "#D97706"
    warning-bg: "#FEF3C7"
    error: "#D92D20"
    error-bg: "#FEE2E2"
typography:
  fontFamily:
    heading: "Nunito, sans-serif"
    base: "Inter, sans-serif"
  headline-display:
    fontFamily: "Nunito"
    fontSize: "36px"        # Desktop hero headline (32px on mobile)
    fontWeight: 800
    lineHeight: "44px"
    letterSpacing: "-0.25px"
  headline-lg:
    fontFamily: "Nunito"
    fontSize: "26px"
    fontWeight: 700
    lineHeight: "32px"
    letterSpacing: "-0.15px"
  headline-md:
    fontFamily: "Nunito"
    fontSize: "20px"
    fontWeight: 700
    lineHeight: "28px"
    letterSpacing: "-0.15px"
  body-lg:
    fontFamily: "Inter"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: "24px"
    letterSpacing: "0px"
  body-md:
    fontFamily: "Inter"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: "23px"
    letterSpacing: "0px"
  body-sm:
    fontFamily: "Inter"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: "21px"
    letterSpacing: "0px"
  label-lg:
    fontFamily: "Inter"
    fontSize: "16px"
    fontWeight: 700
    lineHeight: "24px"
  label-md:
    fontFamily: "Inter"
    fontSize: "15px"
    fontWeight: 600
    lineHeight: "22px"
  label-sm:
    fontFamily: "Inter"
    fontSize: "13px"
    fontWeight: 600
    lineHeight: "18px"
rounded:
  none: "0px"               # Rectangular buttons, navbar, search/form inputs, badges
  sm: "4px"                 # License plate badges, small tags
  md: "8px"                 # Content cards, dashboard panels, modals
  full: "9999px"            # Strictly limited to status dots and user avatars
spacing:
  xs: "10px"
  sm: "20px"
  md: "40px"
  lg: "64px"
  xl: "80px"
components:
  button:
    hero-cta:               # Dedicated high-visibility CTA for "Scan QR Check-In (Guest)"
      backgroundColor: "#FFD717"
      color: "#212121"
      borderColor: "#FFD717"
      borderRadius: "0px"
      borderWidth: "1px"
      borderStyle: "solid"
      padding: "12px 24px"
      fontSize: "16px"
      fontWeight: 700
      minWidth: "200px"
      minHeight: "48px"
      boxShadow: "none"
      fontFamily: "Inter"
    primary:                # Primary action on light surfaces (sharp rectangular)
      backgroundColor: "#0D63A5"
      color: "#FFFFFF"
      borderColor: "#0D63A5"
      borderRadius: "0px"
      borderWidth: "1px"
      borderStyle: "solid"
      padding: "10px 20px"
      fontSize: "15px"
      fontWeight: 700
      minWidth: "150px"
      minHeight: "47px"
      boxShadow: "none"
      fontFamily: "Inter"
    primary-outline:        # Broadcom-style outlined button on white surfaces
      backgroundColor: "transparent"
      color: "#0D63A5"
      borderColor: "#0D63A5"
      borderRadius: "0px"
      borderWidth: "1px"
      borderStyle: "solid"
      padding: "10px 20px"
      fontSize: "15px"
      fontWeight: 700
      minWidth: "150px"
      minHeight: "47px"
      boxShadow: "none"
      fontFamily: "Inter"
    secondary-dark:         # Broadcom-style white outlined button on dark hero/CTA bands
      backgroundColor: "transparent"
      color: "#FFFFFF"
      borderColor: "#FFFFFF"
      borderRadius: "0px"
      borderWidth: "1px"
      borderStyle: "solid"
      padding: "10px 20px"
      fontSize: "15px"
      fontWeight: 700
      minWidth: "164px"
      minHeight: "47px"
      boxShadow: "none"
      fontFamily: "Inter"
    link:
      backgroundColor: "transparent"
      color: "#0D63A5"
      borderRadius: "0px"
      borderWidth: "0px"
      padding: "0px"
      fontSize: "15px"
      fontWeight: 600
      textDecoration: "underline"
      fontFamily: "Inter"
  card:
    backgroundColor: "#FFFFFF"
    borderColor: "#E5E7EB"
    borderRadius: "8px"
    borderWidth: "1px"
    borderStyle: "solid"
    padding: "24px"
    boxShadow: "none"
    textColor: "#494A5E"
---

# 1. Overview & Visual Philosophy (Broadcom-Inspired)

Parkirin adopts a **clean, authoritative, and restrained enterprise marketing aesthetic** modeled after **Broadcom.com**, tailored to Parkirin's brand identity (*Aman, Fleksibel, Profesional, Terpercaya*).

Core visual characteristics:
- **White Chrome Header**: Clean white top bar with understated navigation links, crisp `1px` bottom border (`#E5E7EB`), and sharp rectangular auth buttons.
- **Dark Editorial Hero**: High-contrast dark surface (`#212121` to `#083D68` subtle flat/editorial backdrop) with a crisp left-aligned value proposition, an unmistakable rectangular **Traffic Yellow (`#FFD717`)** CTA for Guest Self Check-In, and a clean right-side system visual/preview.
- **Alternating Flat Content Bands**: Generous vertical whitespace (`py-16` to `py-20`), alternating between pure white (`#FFFFFF`) and soft off-white (`#F9F9F9`) bands.
- **Sharp Rectangular Geometry**: Buttons, inputs, and top utility bars use `0px` (`rounded-none`) corners—never pill-shaped buttons or flashy gradients. Content cards use a disciplined `8px` (`rounded-lg`) radius with `1px solid #E5E7EB` borders and **zero drop shadows by default**.

---

# 2. Color System (60-30-10 Parkirin Palette + Broadcom Restraint)

The color palette is intentionally disciplined to maintain B2B SaaS credibility:
- **Primary (`#0D63A5` — Parkirin Blue)**: Key brand accents, links, active states, primary buttons on light surfaces, and icon strokes.
- **Secondary Surface (`#FFFFFF` & `#F9F9F9`)**: Clean editorial backgrounds for content grids, tables, and authentication forms.
- **Text Hierarchy (`#212121` & `#494A5E`)**: Use Charcoal Black (`#212121`) for headings and high-contrast titles; use Broadcom-inspired Muted Slate (`#494A5E`) for calm, scannable body copy, card descriptions, and utility links.
- **Accent (`#FFD717` — Traffic Yellow)**: Used **sparingly** as a high-visibility signal—specifically for the primary Hero CTA (*Scan QR Check-In Guest*), active indicator lines, or urgent field-operator highlights. Never flood whole sections with yellow.
- **Borders (`#E5E7EB`)**: Subtle structural dividers for cards, tables, and headers.

---

# 3. Typography

- **Headings & Brand (`Nunito`)**: Bold (`700`) and ExtraBold (`800`), compact line heights, tight letter spacing (`-0.25px`) for authoritative section headers, hero headlines, and KPI numbers.
- **Body, Tables, Forms & Buttons (`Inter`)**: Calm Regular (`400`) for body copy; SemiBold (`600`) / Bold (`700`) for terse button labels, form labels, and navigation items.
- **License Plate Badge**: `Inter` SemiBold, uppercase, `tracking-widest` (`0.08em`), sharp `4px` corners, `#212121` border, mimicking Indonesian license plates.

---

# 4. Layout & Landing Page Blueprint (8 Sections — Updated Business Model)

The public Landing Page (`/`) is built from **stacked full-width horizontal bands** with centered `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8` containers and generous vertical spacing:

1. **Top Header (White Chrome)**:
   - White background, `border-b border-[#E5E7EB]`, sticky top.
   - Left: Parkirin Logo. Center: Plain `Inter` links (`Tentang`, `Fitur`, `Cara Kerja`, `Keunggulan`, `Testimoni`). Right: Rectangular outlined `Masuk` (`#0D63A5`) & solid `Daftar` (`#0D63A5`) buttons (`rounded-none`).
2. **Hero Section (Dark Editorial Band — `#212121` / `#083D68`)**:
   - Split 2-column layout (Text left-aligned 60%, clean UI/QR preview right 40%).
   - Headline (`Nunito` 800, white): Concise, enterprise-grade positioning for *Sistem Parkir Portabel Berbasis Web untuk Lahan Swadaya Off-Street*.
   - Subcopy (`Inter` 400, `#E5E7EB`): Highlights zero-hardware setup (no thermal printer, no OCR friction, 100% private tenant data).
   - **Primary CTA (Mandatory)**: Large, high-contrast rectangular button (`rounded-none`, `min-h-[52px]`, bg `#FFD717`, text `#212121` bold, full-width on mobile / inline on desktop): **"Scan QR Check-In (Guest)"** linking to `/in/1` (or public check-in entry).
   - **Secondary CTA**: Broadcom-style transparent white-outlined rectangular button (`border-white text-white rounded-none`): **"Masuk / Daftar Mitra"**.
3. **About Us (White Band `#FFFFFF`)**:
   - Centered concise intro + 4-column flat border card grid (`border-[#E5E7EB] rounded-lg shadow-none`) highlighting the 4 brand pillars (**Aman**, **Fleksibel**, **Profesional**, **Terpercaya**) with clean `24px` line icons in `#0D63A5`.
4. **Fitur Utama (Soft Off-White Band `#F9F9F9`)**:
   - Broadcom-style technology tile grid (3 columns on desktop, 1 on mobile). Flat white cards (`#FFFFFF`, `border-[#E5E7EB]`, `rounded-lg`, `p-6`, `shadow-none`) with top blue icon, bold `Nunito` title, and 2-line `Inter` description:
     1. *Self-Service QR Check-In* (Scan poster QR gerbang tanpa install aplikasi & tanpa login).
     2. *Karcis Digital & HTML5 Web Scanner* (Tanpa printer thermal; petugas cukup scan QR dari browser HP).
     3. *Auto-Lunas Instan & Post-Audit* (Upload bukti QRIS langsung dapat akses; Admin audit belakangan).
     4. *Member Multi-Tier & Booking Inap* (Kuota masuk harian fleksibel + reservasi inap $\ge 2$ hari).
     5. *Analitik Pendapatan YoY & Anti-Fraud* (Pantau pertumbuhan tahunan, jam sibuk, dan audit setoran shift).
     6. *Isolasi Data Multi-Tenant Privat* (Data keuangan 100% milik pengelola lahan, tanpa campur tangan pihak luar).
5. **Cara / Alur Kerja (White Band `#FFFFFF`)**:
   - Structured 4-step horizontal flow (numbered square badges `01`–`04` in `#0D63A5`, sharp corners `rounded-none`, separated by crisp thin lines): *Scan Poster QR di Gerbang* $\rightarrow$ *Input Plat & Dapat Karcis Digital* $\rightarrow$ *Parkir Aman & Transparan* $\rightarrow$ *Scan Keluar & Hitung Tarif Otomatis*.
6. **Keunggulan (Soft Off-White Band `#F9F9F9`)**:
   - 2-column editorial layout: Left side concise headline + bullet highlights (Modal Rp0 tanpa beli alat, Khusus Lahan Swadaya *Off-Street*, Anti Pass-Back & SOP Karcis Hilang); Right side clean flat metrics/comparison card (`border-[#E5E7EB]`).
7. **Testimoni (White Band `#FFFFFF`)**:
   - Clean 3-column editorial testimonial cards (flat white, `1px` border `#E5E7EB`, `rounded-lg`, subtle left blue accent bar `border-l-4 border-l-[#0D63A5]`) featuring real personas (Komuter Stasiun & Pemilik Lahan Swadaya). Keep motion subtle or static to preserve Broadcom's calm authority.
8. **CTA & Kontak + Footer (Dark High-Contrast Band `#0D63A5` $\rightarrow$ Footer `#212121`)**:
   - Full-width `#0D63A5` CTA band: Crisp white `Nunito` headline, concise 1-line subtext, and Broadcom-style rectangular white-outlined button (`Mulai Sekarang`) + yellow-accented button (`Coba Self Check-In`).
   - Footer (`#212121`): Clean multi-column sitemap links, contact info, and copyright notice in muted gray (`#D1D5DB`).

---

# 5. Auth & Application UI Rules (Login, Register & Dashboards)

- **Login & Register Pages (`/login`, `/register`)**:
  - Split-screen or centered flat card (`bg-white border border-[#E5E7EB] rounded-lg shadow-none p-8`) over `#F9F9F9` background.
  - Rectangular inputs (`rounded-none` or `rounded-[4px]`, `border-[#D1D5DB]`, focus border `#0D63A5`).
  - **No phone number (`no_telepon`) field on Register**. Keep Register strictly minimal: Nama Lengkap, Email, Username, Password + Google OAuth button (outlined rectangular button with Google icon).
- **Dashboard & Petugas Web UI**:
  - Maintain flat bordered cards (`border-[#E5E7EB] rounded-lg shadow-none`), compact scannable tables, and large rectangular high-contrast buttons (`min-h-[48px]`) for Petugas field actions.

---

# 6. Tailwind CSS Configuration (`/backend/tailwind.config.js`)

```javascript
theme: {
  extend: {
    colors: {
      primary: '#0D63A5',
      'primary-dark': '#083D68',
      secondary: '#F9F9F9',
      slatebody: '#494A5E',
      accent: '#FFD717',
      dark: '#212121',
      borderline: '#E5E7EB',
    },
    fontFamily: {
      heading: ['Nunito', 'sans-serif'],
      sans: ['Inter', 'sans-serif'],
    },
  },
}