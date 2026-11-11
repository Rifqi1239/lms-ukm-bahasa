
<aside class="admin-sidebar">

    @php
        $isAdmin = auth()->user()->role === 'admin';
    @endphp

    <!-- BRAND -->
    <a href="{{ route('admin.dashboard') }}" class="admin-brand">

        <img
            src="{{ asset('assets/images/logo-ukm-bahasa.png') }}"
            alt="Logo UKM Bahasa"
            class="sidebar-logo"
        >

        <div class="admin-brand-text">
            <span>LMS UKM Bahasa</span>
            <small>{{ $isAdmin ? 'Administrator' : 'Pengajar' }}</small>
        </div>

    </a>

    <div class="admin-sidebar-divider"></div>

    <p class="admin-sidebar-label">MENU UTAMA</p>

    <!-- NAVIGASI -->
    <nav class="admin-sidebar-nav">

        <!-- DASHBOARD: ADMIN DAN PENGAJAR -->
        <a
            href="{{ route('admin.dashboard') }}"
            class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        >
            <span class="admin-menu-icon">▦</span>
            <span>Dashboard</span>
        </a>

        @if ($isAdmin)

            <!-- PENGGUNA: ADMIN SAJA -->
            <a
                href="{{ route('admin.members.index') }}"
                class="admin-sidebar-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}"
            >
                <span class="admin-menu-icon">♙</span>
                <span>Pengguna</span>
            </a>

            <!-- KELOMPOK BAHASA: ADMIN SAJA -->
            <a
                href="{{ route('admin.language-groups.index') }}"
                class="admin-sidebar-link {{ request()->routeIs('admin.language-groups.*') ? 'active' : '' }}"
            >
                <span class="admin-menu-icon">▤</span>
                <span>Kelompok Bahasa</span>
            </a>

        @endif

        <!-- KELOLA MODUL: ADMIN DAN PENGAJAR -->
        <a
            href="{{ route('admin.modules.index') }}"
            class="admin-sidebar-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}"
        >
            <span class="admin-menu-icon">▣</span>
            <span>Kelola Modul</span>
        </a>

        <!-- SOAL LATIHAN: ADMIN DAN PENGAJAR -->
        <a
            href="{{ route('admin.questions.index') }}"
            class="admin-sidebar-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}"
        >
            <span class="admin-menu-icon">☷</span>
            <span>Soal Latihan</span>
        </a>

        <!-- HASIL BELAJAR: ADMIN DAN PENGAJAR -->
        <a
            href="{{ route('admin.learning-results.index') }}"
            class="admin-sidebar-link {{ request()->routeIs('admin.learning-results.*') ? 'active' : '' }}"
        >
            <span class="admin-menu-icon">▥</span>
            <span>Hasil Belajar</span>
        </a>

        <!-- PENILAIAN AKHIR: ADMIN DAN PENGAJAR -->
        <a
            href="{{ route('admin.final-grades.index') }}"
            class="admin-sidebar-link {{ request()->routeIs('admin.final-grades.*') ? 'active' : '' }}"
        >
            <span class="admin-menu-icon">✓</span>
            <span>Penilaian Akhir</span>
        </a>

    </nav>

    <!-- PROFIL DAN LOGOUT -->
    <div class="admin-sidebar-bottom">

        <div class="admin-profile">

            <div class="admin-profile-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div class="admin-profile-info">
                <span>{{ auth()->user()->name }}</span>
                <small>{{ $isAdmin ? 'Administrator' : 'Pengajar' }}</small>
            </div>

        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit" class="admin-logout-button">
                Keluar dari Sistem
            </button>
        </form>

    </div>

</aside>
