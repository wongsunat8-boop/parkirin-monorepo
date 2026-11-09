# DESIGN-SYSTEM.md - Parkirin Visual Identity & UI Guidelines

## 1. Brand Characteristics & Design Philosophy
**Parkirin** uses a clean, modern, high-contrast visual identity built around four core brand characteristics:
1. **Aman (Secure)**: Clear validation states, official digital/QR ticket receipts, and transparent status indicators that give riders and parking owners peace of mind.
2. **Fleksibel (Flexible)**: Adaptive interfaces that seamlessly support multiple tariff models (`Per Jam`, `Flat Harian`, `Progresif Mal`, `Tarif Inap`), multi-tenant configurations, and both OCR camera and manual input workflows.
3. **Profesional (Professional)**: Clean, structured, **intuitive, concise, and to-the-point** layouts inspired by **shadcn/ui** and **Refero** design patterns—free of visual clutter, unnecessary animations, or bloated menus.
4. **Terpercaya (Trustworthy)**: Upfront quota and tariff transparency before booking, immutable transaction logs, and high-contrast typography for fast outdoor readability.

> **Core UI Directive**: Every screen across Web and Mobile MUST be **intuitive, concise, to the point, responsive, and fast-loading**. Avoid decorative bloat so users (especially field `petugas` and rushed commuters) can complete tasks in seconds.

---

## 2. Color Palette (60-30-10 Rule)
All Web (Tailwind CSS) and Mobile (Flutter `ThemeData`) components MUST strictly use these hex values:

| Role | Name | Hex Code | Brand Meaning & Usage Context |
| :--- | :--- | :--- | :--- |
| **Primary (60% Brand/Identity)** | Parkirin Blue | `#0D63A5` | Represents **Security & Trust** (*Keamanan & Kepercayaan*). Used for primary buttons, active navigation states, top app bars, brand headers, and key highlights. |
| **Secondary (30% Surface)** | Soft Off-White | `#F9F9F9` | Represents **Professionalism** (*Profesionalisme*). Used for main page backgrounds, scaffold backgrounds, subtle card containers, and input field backgrounds. |
| **Accent (10% Highlight)** | Traffic Yellow | `#FFD717` | Represents **Flexibility & Traffic Guidance** (*Fleksibilitas*). Used for secondary CTA buttons, badges, booking status highlights, directional cues, and warnings. |
| **Neutral Dark** | Charcoal Black | `#212121` | Primary body text, headings, icons, license plate badges, dark surfaces, and footer background. |
| **Pure White** | Surface White | `#FFFFFF` | Elevated cards, modals, table rows, and text on Primary Blue buttons. |

### Semantic Status Colors
- **Success (Available / Sudah Bayar / Disetujui)**: `#16A34A` (Text/Icon), `#DCFCE7` (Badge BG)
- **Warning (Pending / Menunggu / Almost Full)**: `#D97706` (Text/Icon), `#FEF3C7` (Badge BG)
- **Danger (Banned / Penuh / Batal / Belum Bayar)**: `#DC2626` (Text/Icon), `#FEE2E2` (Badge BG)

---

## 3. Typography
Parkirin pairs a friendly, geometric rounded display font for branding/headings with a highly legible sans-serif for dense UI data and tables.

### A. Brand & Heading Typeface: **Nunito**
- **Usage**: Page Titles, Hero Headings, Section Headers, Modal Titles, Price/Quota Numbers.
- **Weights**: `Light (300)`, `Regular (400)`, `Medium (500)`, `Bold (700)`, `Black (900)`.

### B. UI & Body Typeface: **Inter**
- **Usage**: Body copy, data tables, form labels, input fields, navigation links, buttons, receipts.
- **Weights**: `Light (300)`, `Regular (400)`, `Medium (500)`, `SemiBold (600)`, `Bold (700)`, `ExtraBold (800)`.

---

## 4. UI Component References (shadcn/ui, 21st.dev, & Refero)
The UI implementation draws inspiration from **shadcn/ui**, **Refero**, and **21st.dev**. Additional component references may be provided iteratively by the user during implementation phases.

### 4.1. Dashboard & Application UI (`shadcn/ui` & `Refero` Aesthetic)
- **Cards & Containers**: Crisp `1px` borders (`border-slate-200`), subtle shadows (`shadow-sm`), rounded corners (`rounded-xl`), and clean `#FFFFFF` surfaces over `#F9F9F9` backgrounds.
- **Data Tables**: Compact, scannable tables with sticky headers, clear semantic status badges, inline action buttons, and instant search/filter bars.
- **Forms & Dialogs**: Focused, single-purpose modals and forms with clear validation feedback and zero visual clutter.

### 4.2. Public Landing Page Sections (`21st.dev` Component References)
The public Landing Page (`/` in `/backend`) consists of 8 sections (`Hero Section`, `About Us`, `Fitur`, `Cara/Alur Kerja`, `Keunggulan`, `Testimoni`, `CTA & Kontak`, `Footer`) and MUST adapt the following specific **21st.dev** components into Blade + Tailwind CSS:
1. **Testimoni Section (`testimonials-with-marquee`)**:
   - **Reference URL**: `https://21st.dev/@serafimcloud/components/testimonials-with-marquee`
   - **Adaptation**: Infinite horizontal CSS marquee scrolling cards featuring testimonials from commuters (`Pengendara`) and parking operators (`Mitra Tenant / Owner`) with avatar, name, role badge, and quote, flanked by soft left/right fade masks.
2. **CTA & Kontak Section (`cta-with-marquee`)**:
   - **Reference URL**: `https://21st.dev/@lyanchouss/components/cta-with-marquee`
   - **Adaptation**: High-contrast Call-to-Action block combining a bold Nunito heading, primary/accent CTA buttons (`Mulai Parkir Sekarang` / `Daftarkan Lahan Parkir`), contact info, and a dynamic marquee strip highlighting Parkirin's core advantages (`Aman`, `Fleksibel`, `Profesional`, `Terpercaya`, `Scan OCR Cepat`, `Karcis QR Digital`).

---

## 5. Platform-Specific Implementation Configs

### 5.1. Website (Tailwind CSS Config in `/backend`)
Ensure `tailwind.config.js` defines:
```javascript
theme: {
  extend: {
    colors: {
      primary: '#0D63A5',
      secondary: '#F9F9F9',
      accent: '#FFD717',
      dark: '#212121',
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
    },
    animation: {
      marquee: 'marquee 30s linear infinite',
    },
  },
}
```

### 5.2. Mobile App (Flutter Theme Config in `/mobile`)
Ensure `lib/core/theme/app_theme.dart` defines:
- `primaryColor`: `Color(0xFF0D63A5)`
- `scaffoldBackgroundColor`: `Color(0xFFF9F9F9)`
- `colorScheme`: `ColorScheme.fromSeed(seedColor: Color(0xFF0D63A5), primary: Color(0xFF0D63A5), secondary: Color(0xFFFFD717), surface: Color(0xFFF9F9F9))`
- Use `google_fonts` package to apply `GoogleFonts.nunito()` for `display*` / `headline*` / `title*` and `GoogleFonts.inter()` for `body*` / `label*`.

---

## 6. UX, Responsiveness & Performance Standards
1. **Responsive Across All Viewports**: Web panels must adapt cleanly to mobile, tablet, and desktop screens (collapsible navigation drawer on mobile, responsive grid cards, and horizontal scroll wrappers for data tables).
2. **Fast Loading & Lightweight Assets**: Avoid bloated third-party JS libraries; rely on native CSS animations, lightweight SVG icons (Lucide/Heroicons), and skeleton states on Flutter mobile screens.
3. **Minimal Typing for Petugas**: Make field action buttons (`Scan OCR`, `Scan Karcis`, `Verifikasi Booking`, `Input Manual`, `Konfirmasi Bayar`) large (min height `52px` on mobile) with high contrast (`#0D63A5` or `#FFD717` with `#212121` bold text).
4. **License Plate Badge Style**: Render `plat_nomor` inside a distinctive rectangular badge (`Inter SemiBold`, uppercase, `letter-spacing: 0.08em`) resembling an Indonesian vehicle license plate.
5. **Upfront Quota & Tariff Transparency**: On `Katalog Parkir` cards, always display remaining capacity (`Kapasitas - Terisi`), booking availability status, and tariff details (`Per Jam` / `Flat Harian` / `Progresif Mal` + `Tarif Inap`) prominently before the user clicks `Booking`.