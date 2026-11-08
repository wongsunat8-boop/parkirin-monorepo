<a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Dashboard</a>
<a href="{{ route('admin.tarif.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.tarif.*') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Kelola Tarif</a>
<a href="{{ route('admin.kapasitas.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.kapasitas.*') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Kelola Kapasitas</a>
<a href="{{ route('admin.petugas.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.petugas.*') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Kelola Petugas</a>
<a href="{{ route('admin.kendaraan.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.kendaraan.*') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Kelola Kendaraan</a>
<a href="{{ route('admin.pengendara.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.pengendara.*') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Daftar Pengendara</a>
<a href="{{ route('admin.log-aktivitas.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.log-aktivitas.*') ? 'bg-primary text-white' : 'hover:bg-gray-100' }}">Log Aktivitas</a>
