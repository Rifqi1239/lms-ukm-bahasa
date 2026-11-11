<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $module->title }} | LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="lms-page">

    <!-- NAVBAR -->
    <nav class="lms-navbar">

        <a href="{{ url('/dashboard') }}" class="brand">
            <img
                src="{{ asset('assets/images/logo-ukm-bahasa.png') }}"
                alt="Logo UKM Bahasa"
                class="sidebar-logo">

            <span>UKM Bahasa</span>
        </a>

        <div class="nav-links">

            <a href="{{ route('dashboard') }}" class="nav-link">
                Dashboard
            </a>

            <a href="{{ route('modules.index') }}"
                class="nav-link active">
                Modul
            </a>

            <a
                href="{{ route('quiz.history') }}"
                class="nav-link {{ request()->routeIs('quiz.history') ? 'active' : '' }}">
                Riwayat Latihan
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

        <!-- NAVIGASI KEMBALI -->
        <div class="module-detail-breadcrumb">
            <a href="{{ route('modules.index') }}">
                ← Kembali ke Modul
            </a>
        </div>

        <!-- DESKRIPSI MODUL -->
        <section class="module-detail">

            <!-- COVER -->
            <div class="module-detail-cover">

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

            <!-- INFORMASI -->
            <div class="module-detail-info">

                <span class="module-exercise-label">
                    {{ $module->questions_count > 0
                        ? 'Ada latihan'
                        : 'Tanpa latihan' }}
                </span>

                <h1>{{ $module->title }}</h1>

                <p class="module-detail-group">
                    Kelompok Bahasa:
                    <strong>
                        {{ $module->languageGroup?->name ?? 'Belum ditentukan' }}
                    </strong>
                </p>

                <div class="module-detail-description">

                    <h2>Deskripsi Modul</h2>

                    <p>
                        {{ $module->description ?? 'Belum ada deskripsi untuk modul ini.' }}
                    </p>

                </div>

                <div class="module-detail-stat">

                    <div>
                        <span>Jumlah Soal</span>
                        <strong>{{ $module->questions_count }} Soal</strong>
                    </div>

                    <div>
                        <span>Status Latihan</span>
                        <strong>
                            {{ $module->questions_count > 0
                                ? 'Tersedia'
                                : 'Belum tersedia' }}
                        </strong>
                    </div>

                </div>

                <!-- TOMBOL AKSI -->
                <div class="module-detail-actions">

                    <a
                        href="{{ route('modules.read', $module) }}"
                        class="button">
                        Mulai Membaca
                    </a>

                    @if ($module->questions_count > 0)

                    <a
                        href="{{ route('quiz.start', $module) }}"
                        class="button button-outline">
                        Mulai Latihan
                    </a>

                    @endif

                </div>

            </div>

        </section>

    </main>

</body>

</html>