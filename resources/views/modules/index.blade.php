<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Modul Belajar - LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="lms-page">

    <!-- Navigasi utama -->
    <nav class="lms-navbar">

        <a href="{{ url('/dashboard') }}" class="brand">
            <img
                src="{{ asset('assets/images/logo-ukm-bahasa.png') }}"
                alt="Logo UKM Bahasa"
                class="sidebar-logo">

            <span>UKM Bahasa</span>
        </a>

        <div class="nav-links">

            <a href="{{ route('dashboard') }}"
                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('modules.index') }}"
                class="nav-link {{ request()->routeIs('modules.*') ? 'active' : '' }}">
                Modul
            </a>

            <a
                href="{{ route('quiz.history') }}"
                class="nav-link {{ request()->routeIs('quiz.history') ? 'active' : '' }}">
                Riwayat Latihan
            </a>

            <a
                href="{{ route('member.final-grades.index') }}"
                class="nav-link {{ request()->routeIs('member.final-grades.*') ? 'active' : '' }}">
                Penilaian Akhir
            </a>

        </div>

        <div class="nav-user">

            <div class="group-badge">
                Kelompok:
                {{ auth()->user()->languageGroup?->name ?? 'Belum ada kelompok' }}
                ({{ auth()->user()->languageGroup?->code ?? '-' }})
            </div>

            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <span class="user-name">
                {{ auth()->user()->name }}
            </span>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit" class="button button-outline">
                    Logout
                </button>
            </form>

        </div>

    </nav>

    <main class="modules-container">

        <header class="modules-header">

            <h1>Modul Belajar</h1>

            <p>
                Pelajari materi sesuai kelompok bahasa kamu:
                <strong>
                    {{ auth()->user()->languageGroup?->name ?? 'Belum ada kelompok' }}
                </strong>
            </p>

        </header>

        <!-- PENCARIAN, FILTER, DAN PENGURUTAN -->
        <form action="{{ route('modules.index') }}"
            method="GET"
            class="modules-toolbar">

            <input
                type="search"
                name="search"
                placeholder="Cari modul belajar..."
                value="{{ request('search') }}"
                class="modules-search">

            <div class="modules-filters">

                <a
                    href="{{ route('modules.index', ['search' => request('search')]) }}"
                    class="{{ !request('filter') ? 'active' : '' }}">
                    Semua
                </a>

                <a
                    href="{{ route('modules.index', ['search' => request('search'), 'filter' => 'exercise']) }}"
                    class="{{ request('filter') === 'exercise' ? 'active' : '' }}">
                    Ada Latihan
                </a>

                <a
                    href="{{ route('modules.index', ['search' => request('search'), 'filter' => 'no-exercise']) }}"
                    class="{{ request('filter') === 'no-exercise' ? 'active' : '' }}">
                    Tanpa Latihan
                </a>

            </div>

            <select
                name="sort"
                class="modules-sort"
                onchange="this.form.submit()">

                <option value="newest"
                    {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>
                    Terbaru
                </option>

                <option value="oldest"
                    {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                    Terlama
                </option>

            </select>

            <button type="submit" class="button">
                Cari
            </button>

        </form>

        <!-- DAFTAR MODUL -->
        <section class="modules-list">

            @forelse ($modules as $module)

            <article class="module-item">

                <!-- COVER MODUL -->
                <div class="module-cover">

                    @if ($module->cover_image)

                    <img
                        src="{{ asset('storage/' . $module->cover_image) }}"
                        alt="{{ $module->title }}">

                    @else

                    <div class="module-cover-placeholder">
                        {{ strtoupper(substr($module->title, 0, 1)) }}
                    </div>

                    @endif

                </div>

                <!-- INFORMASI MODUL -->
                <div class="module-info">

                    <h2>{{ $module->title }}</h2>

                    <p>
                        {{ $module->description ?? 'Belum ada deskripsi modul.' }}
                    </p>

                    <!-- STATUS BERDASARKAN JUMLAH SOAL AKTUAL -->
                    <span class="module-exercise-label">
                        {{ $module->questions_count > 0
                                ? 'Ada latihan'
                                : 'Tanpa latihan' }}
                    </span>

                </div>

                <!-- JUMLAH SOAL DAN TOMBOL BACA -->
                <div class="module-count">

                    <span>
                        {{ $module->questions_count }}
                        soal
                    </span>

                    <a
                        href="{{ route('modules.show', $module) }}"
                        class="module-arrow"
                        aria-label="Lihat deskripsi {{ $module->title }}"
                        title="Lihat deskripsi modul">
                        ›
                    </a>

                </div>

            </article>

            @empty

            <div class="modules-empty">

                <h2>
                    @if (request('filter') === 'exercise')
                    Belum ada modul dengan latihan
                    @elseif (request('filter') === 'no-exercise')
                    Tidak ada modul tanpa latihan
                    @else
                    Belum ada modul belajar
                    @endif
                </h2>

                <p>
                    @if (request('filter') === 'exercise')
                    Modul yang sudah memiliki soal latihan akan muncul di sini.
                    @elseif (request('filter') === 'no-exercise')
                    Semua modul yang tersedia mungkin sudah memiliki soal latihan.
                    @else
                    Modul untuk kelompok bahasa kamu akan muncul di sini
                    setelah tersedia.
                    @endif
                </p>

            </div>

            @endforelse

        </section>

    </main>

</body>

</html>