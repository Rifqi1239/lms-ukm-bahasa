
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="lms-page">

    <!-- Navigasi utama -->
    <nav class="lms-navbar">
        <a href="{{ route('dashboard') }}" class="brand">
            <img
                src="{{ asset('assets/images/logo-ukm-bahasa.png') }}"
                alt="Logo UKM Bahasa"
                class="sidebar-logo">

            <span>UKM Bahasa</span>
        </a>

        <div class="nav-links">
            <a
                href="{{ route('dashboard') }}"
                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a
                href="{{ route('modules.index') }}"
                class="nav-link {{ request()->routeIs('modules.*') ? 'active' : '' }}">
                Modul
            </a>

            <a
                href="{{ route('quiz.history') }}"
                class="nav-link {{ request()->routeIs('quiz.history', 'quiz.result') ? 'active' : '' }}">
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

    <main class="dashboard-container">

        <!-- Sapaan -->
        <section class="welcome-section">
            <p class="eyebrow">DASHBOARD ANGGOTA</p>

            <h1>
                Selamat datang kembali, {{ auth()->user()->name }}!
            </h1>

            <p class="welcome-description">
                Temukan materi baru dan lanjutkan perjalanan belajarmu
                bersama UKM Bahasa.
            </p>
        </section>

        <!-- Lanjutkan membaca -->
        <section class="continue-section">
            <div class="section-heading">
                <div>
                    <h2>Lanjutkan Membaca</h2>
                    <p>Teruskan materi yang terakhir kamu pelajari.</p>
                </div>
            </div>

            @if ($continueModule && $continueProgress)

                <article class="continue-card">

                    <!-- Sampul modul terakhir dibaca -->
                    <div class="book-cover cover-arab">

                        @if (
                            $continueModule->cover_image &&
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($continueModule->cover_image)
                        )

                            <img
                                src="{{ asset('storage/' . $continueModule->cover_image) }}"
                                alt="Sampul {{ $continueModule->title }}"
                                class="module-cover-image"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                            <div class="module-cover-fallback" style="display: none;">
                                <span class="cover-label">UKM BAHASA</span>

                                <span class="cover-title">
                                    {{ $continueModule->languageGroup?->name ?? 'Materi Bahasa' }}
                                </span>

                                <span class="cover-subtitle">
                                    Materi Pembelajaran
                                </span>
                            </div>

                        @else

                            <div class="module-cover-fallback">
                                <span class="cover-label">UKM BAHASA</span>

                                <span class="cover-title">
                                    {{ $continueModule->languageGroup?->name ?? 'Materi Bahasa' }}
                                </span>

                                <span class="cover-subtitle">
                                    Materi Pembelajaran
                                </span>
                            </div>

                        @endif

                    </div>

                    <!-- Informasi progres -->
                    <div class="continue-content">

                        <span class="content-category">
                            {{ strtoupper($continueModule->languageGroup?->name ?? 'MATERI') }}
                        </span>

                        <h3>{{ $continueModule->title }}</h3>

                        <p>
                            {{ $continueModule->description
                                ?: 'Lanjutkan pembelajaran dari materi yang terakhir kamu baca.' }}
                        </p>

                        <div class="reading-progress">
                            <div class="progress-info">
                                <span>Progres membaca</span>

                                <span>
                                    {{ $continueProgress->progress_percentage }}%
                                </span>
                            </div>

                            <div class="progress-track">
                                <div
                                    class="progress-value"
                                    style="width: {{ $continueProgress->progress_percentage }}%;">
                                </div>
                            </div>

                            <p>
                                Halaman {{ $continueProgress->current_page }}
                                dari {{ $continueProgress->total_pages }}
                            </p>
                        </div>

                        <a
                            href="{{ route('modules.read', $continueModule) }}"
                            class="button button-primary">
                            Lanjutkan Membaca
                            <span aria-hidden="true">→</span>
                        </a>

                    </div>
                </article>

            @else

                <article class="continue-card">

                    <div class="book-cover cover-arab">
                        <span class="cover-label">UKM BAHASA</span>

                        <span class="cover-title">
                            Mulai<br>Belajar
                        </span>

                        <span class="cover-subtitle">
                            Pembelajaran Bahasa
                        </span>
                    </div>

                    <div class="continue-content">

                        <span class="content-category">
                            BELUM ADA PROGRES
                        </span>

                        <h3>Belum Ada Materi yang Dibaca</h3>

                        <p>
                            Kamu belum memiliki riwayat membaca materi.
                            Yuk, mulai belajar dengan memilih modul yang tersedia.
                        </p>

                        <a
                            href="{{ route('modules.index') }}"
                            class="button button-primary">
                            Jelajahi Modul
                            <span aria-hidden="true">→</span>
                        </a>

                    </div>
                </article>

            @endif
        </section>

        <!-- Modul terbaru -->
        <section class="latest-section">

            <div class="section-heading">
                <div>
                    <h2>Modul Terbaru</h2>
                    <p>Materi terbaru untuk kelompok bahasamu.</p>
                </div>

                <a href="{{ route('modules.index') }}" class="text-link">
                    Lihat semua <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="module-grid">

                @forelse ($modules as $module)

                    <article class="module-card">

                        <!-- Sampul modul -->
                        <div class="module-cover cover-grammar">

                            @if (
                                $module->cover_image &&
                                \Illuminate\Support\Facades\Storage::disk('public')->exists($module->cover_image)
                            )

                                <img
                                    src="{{ asset('storage/' . $module->cover_image) }}"
                                    alt="Sampul {{ $module->title }}"
                                    class="module-cover-image"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                                <div class="module-cover-fallback" style="display: none;">
                                    <span class="cover-label">
                                        MODUL {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    <span class="module-cover-title">
                                        {{ $module->title }}
                                    </span>

                                    <span class="cover-decoration">
                                        {{ $module->languageGroup?->code ?? '' }}
                                    </span>
                                </div>

                            @else

                                <div class="module-cover-fallback">
                                    <span class="cover-label">
                                        MODUL {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    <span class="module-cover-title">
                                        {{ $module->title }}
                                    </span>

                                    <span class="cover-decoration">
                                        {{ $module->languageGroup?->code ?? '' }}
                                    </span>
                                </div>

                            @endif

                        </div>

                        <!-- Informasi modul -->
                        <div class="module-content">

                            <span class="module-tag">
                                {{ $module->languageGroup?->name ?? 'Materi Bahasa' }}
                            </span>

                            <h3>{{ $module->title }}</h3>

                            <p>
                                {{ $module->description
                                    ? \Illuminate\Support\Str::limit($module->description, 100)
                                    : 'Pelajari materi pembelajaran bahasa melalui modul ini.' }}
                            </p>

                            <div class="module-meta">

                                <span>
                                    {{ $module->questions_count }}
                                    soal latihan
                                </span>

                                @if ($module->questions_count > 0)

                                    <span class="exercise-status available">
                                        Ada latihan
                                    </span>

                                @else

                                    <span class="exercise-status unavailable">
                                        Tanpa latihan
                                    </span>

                                @endif

                            </div>

                            <a
                                href="{{ route('modules.show', $module) }}"
                                class="button button-outline">
                                Baca Modul
                                <span aria-hidden="true">→</span>
                            </a>

                        </div>
                    </article>

                @empty

                    <p>
                        Belum ada modul yang tersedia untuk kelompok bahasamu.
                    </p>

                @endforelse

            </div>
        </section>

        <footer class="dashboard-footer">
            <p>UKM Bahasa · Sistem Pembelajaran Bahasa</p>
        </footer>

    </main>

</body>

</html>
