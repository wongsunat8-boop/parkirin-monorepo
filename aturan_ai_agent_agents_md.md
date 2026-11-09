# AGENTS.md - Parkirin AI Agent Guidelines & Rules

## 1. Project Identity & Role
You are an elite Senior Full-Stack Web Developer (Laravel) and Mobile Engineer (Flutter) working on **Parkirin**, a Multi-Tenant Portable Parking SaaS designed for authorized informal/swadaya parking operators.

## 2. Core Directives (MANDATORY)
1. **Read Context First**: Before writing or modifying any code, always check `@PRD.md` for business rules and edge-case handling, `@ARCHITECTURE.md` for directory/route mapping and the 10-table schema, and `@DESIGN-SYSTEM.md` for visual styling and UI component references.
2. **To-The-Point Execution (Zero Scope Creep)**: Development MUST be **intuitive, concise, professional, and strictly to the point** (*jangan melebar ke mana-mana*). Build only the pages, features, and flows defined in the Site Map and MVP scope. Do not invent unrequested menus, bloated animations, or over-engineered abstractions.
3. **Strict Multi-Tenant Isolation (Anti-IDOR)**: Every query in the `owner`, `admin`, and `petugas` scopes MUST be filtered by `id_tempat_parkir`. Implement a Tenant Global Scope or Middleware Authorization so cross-tenant data access is impossible and throws `403 Forbidden`.
4. **Database Schema Lock (10 Tables)**: Stick strictly to the 10 defined tables (`tempat_parkir`, `user`, `pengendara`, `tarif`, `kendaraan`, `pengajuan_banned`, `pengajuan_klaim_kendaraan`, `booking`, `transaksi`, `log_aktivitas`). Do not invent new columns or tables unless explicitly instructed by the user.
5. **Native Mobile Only**: The `/mobile` Flutter project targets **Android & iOS (Native APK)** only. Do not add web-only packages or PWA configurations.
6. **No Lazy Placeholders**: Never leave `// TODO: implement business logic` in controllers, services, or UI views. Deliver complete, working vertical slices.

## 3. Token & Context Efficiency Rules
- **Ignore Heavy Directories**: Never scan or read `/backend/vendor`, `/backend/node_modules`, `/backend/storage`, `/mobile/build`, `/mobile/.dart_tool`, or `/mobile/ios/Pods`.
- **Modular Execution**: Only modify files relevant to the current prompt's vertical slice. Do not refactor unrelated modules.
- **Concise Explanations**: When outputting code, keep conversational explanations brief (bullet points) and focus on what was created/updated and how to test it.

## 4. Coding Standards, Performance & UKK Paket 2 Compliance
- **Laravel (`/backend`)**:
  - **Fast Loading & Query Optimization**: Use Eloquent ORM with eager loading (`with()`) and selective column fetching (`select()`) to prevent $N+1$ query problems. Avoid unnecessary loops and use indexed columns for filtering.
  - **Concurrency & Race Condition Prevention**: Wrap all capacity-mutating operations (Booking creation, Booking cancellation/expiry, Verification, Parking In/Out) inside `DB::transaction()` with row-level locking (`lockForUpdate()`) on `tempat_parkir`.
  - **MySQL Stored Function / Procedure**: Implement a MySQL Stored Function (`fn_hitung_biaya_parkir`) via Laravel migration to calculate parking duration, overnight stays (`DATEDIFF`), and total fee across all tariff models (`Per Jam`, `Flat Harian`, `Progresif Mal`, plus `tarif_inap`) to satisfy UKK rubric point 11b.
  - **Automated System Audit Trail**: Use Eloquent Observers or a centralized service (`ActivityLogService`) to automatically record staff actions (e.g., tariff updates, capacity changes, ban requests/approvals, gate transactions) into `log_aktivitas`.
  - **Hybrid Pagination Rule**: Use Offset pagination (`paginate(10)`) for low-volume master tables (`tempat_parkir`, `user`, `tarif`, `kendaraan`), and Keyset/Cursor pagination (`cursorPaginate(15)`) for high-volume tables (`transaksi`, `booking`, `log_aktivitas`).
- **Flutter (`/mobile`)**:
  - Use a clean, feature-based folder structure under `lib/features/` (`auth`, `pengendara`, `petugas`).
  - Use `google_mlkit_text_recognition` for on-device license plate OCR and `mobile_scanner` for QR ticket/booking scanning.
  - Always display an editable preview dialog after OCR scanning so `petugas` can manually correct misread characters before submitting, and always provide a direct manual input fallback (`Input Manual`).
  - Normalize all license plates (uppercase, strip spaces, e.g., `B1234XYZ`) before sending to the API.