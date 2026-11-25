
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $module->title }} | LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="lms-page">

    @php
        $practices = $module->practices->keyBy('type');

        $independentPractice = $practices->get('independent');
        $assessmentPractice = $practices->get('assessment');

        $independentQuestionCount = $independentPractice
            ? $independentPractice->questions()->count()
            : 0;

        $assessmentQuestionCount = $assessmentPractice
            ? $assessmentPractice->questions()->count()
            : 0;

        $independentAvailable = $independentPractice
            && $independentPractice->is_active
            && $independentQuestionCount > 0;

        $assessmentAvailable = $assessmentPractice
            && $assessmentPractice->isAvailable()
            && $assessmentQuestionCount > 0;
    @endphp

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

            <a href="{{ route('modules.index') }}" class="nav-link active">
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

            <!-- COVER MODUL -->
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

            <!-- INFORMASI MODUL -->
            <div class="module-detail-info">

                <span class="module-exercise-label">
                    {{ ($independentQuestionCount + $assessmentQuestionCount) > 0
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

                <!-- DESKRIPSI -->
                <div class="module-detail-description">

                    <h2>Deskripsi Modul</h2>

                    <p>
                        {{ $module->description ?? 'Belum ada deskripsi untuk modul ini.' }}
                    </p>

                </div>

                <!-- STATISTIK MODUL -->
                <div class="module-detail-stat">

                    <div>
                        <span>Jumlah Soal</span>
                        <strong>
                            {{ $independentQuestionCount + $assessmentQuestionCount }} Soal
                        </strong>
                    </div>

                    <div>
                        <span>Latihan Mandiri</span>
                        <strong>
                            {{ $independentQuestionCount }} Soal
                        </strong>
                    </div>

                    <div>
                        <span>Latihan Penilaian</span>
                        <strong>
                            {{ $assessmentQuestionCount }} Soal
                        </strong>
                    </div>

                </div>

                <!-- TOMBOL MEMBACA -->
                <div class="module-detail-actions">

                    <a
                        href="{{ route('modules.read', $module) }}"
                        class="button">
                        Mulai Membaca
                    </a>

                </div>

            </div>

            <!-- BAGIAN LATIHAN MODUL -->
            <div class="module-practices">

                <div class="module-practices-header">
                    <h2>Latihan Modul</h2>
                    <p>
                        Pilih jenis latihan sesuai kebutuhan pembelajaran.
                    </p>
                </div>

                <div class="module-practice-grid">

                    <!-- KARTU LATIHAN MANDIRI -->
                    <div class="module-practice-card">

                        <div class="module-practice-heading">
                            <h3>Latihan Mandiri</h3>

                            <span class="practice-type-badge independent">
                                Tanpa batas waktu
                            </span>
                        </div>

                        <p class="module-practice-description">
                            Latihan untuk menguji pemahaman materi secara mandiri.
                            Kamu dapat mengerjakannya kapan saja dan mengulanginya
                            tanpa batas.
                        </p>

                        <div class="module-practice-info">
                            <span>Jumlah soal</span>
                            <strong>{{ $independentQuestionCount }} soal</strong>
                        </div>

                        <div class="module-practice-info">
                            <span>Waktu pengerjaan</span>
                            <strong>Bebas</strong>
                        </div>

                        <div class="module-practice-footer">

                            @if ($independentAvailable)

                                <span class="practice-status available">
                                    Latihan tersedia
                                </span>

                                <a
                                    href="{{ route('quiz.start', [
                                        'module' => $module->id,
                                        'type' => 'independent'
                                    ]) }}"
                                    class="button button-outline practice-action">
                                    Mulai Latihan Mandiri
                                </a>

                            @else

                                <span class="practice-status unavailable">
                                    Latihan belum tersedia
                                </span>

                                <button
                                    type="button"
                                    class="button button-outline practice-action"
                                    disabled>
                                    Belum Tersedia
                                </button>

                            @endif

                        </div>

                    </div>

                    <!-- KARTU LATIHAN PENILAIAN -->
                    <div class="module-practice-card">

                        <div class="module-practice-heading">
                            <h3>Latihan Penilaian</h3>

                            <span class="practice-type-badge assessment">
                                Penilaian akhir
                            </span>
                        </div>

                        <p class="module-practice-description">
                            Latihan yang digunakan sebagai bagian dari penilaian
                            akhir pembelajaran.
                        </p>

                        <div class="module-practice-info">
                            <span>Jumlah soal</span>
                            <strong>{{ $assessmentQuestionCount }} soal</strong>
                        </div>

                        @if ($assessmentPractice)

                            <div class="module-practice-info">
                                <span>Mulai</span>

                                <strong>
                                    {{ $assessmentPractice->start_at
                                        ? $assessmentPractice->start_at->format('d-m-Y H:i')
                                        : 'Belum ditentukan' }}
                                </strong>
                            </div>

                            <div class="module-practice-info">
                                <span>Deadline</span>

                                <strong>
                                    {{ $assessmentPractice->deadline_at
                                        ? $assessmentPractice->deadline_at->format('d-m-Y H:i')
                                        : 'Belum ditentukan' }}
                                </strong>
                            </div>

                            <div class="module-practice-footer">

                                @if ($assessmentAvailable)

                                    <span class="practice-status available">
                                        Penilaian sedang berlangsung
                                    </span>

                                    <a
                                        href="{{ route('quiz.start', [
                                            'module' => $module->id,
                                            'type' => 'assessment'
                                        ]) }}"
                                        class="button button-outline practice-action">
                                        Mulai Latihan Penilaian
                                    </a>

                                @elseif (!$assessmentPractice->is_active)

                                    <span class="practice-status unavailable">
                                        Latihan penilaian dinonaktifkan
                                    </span>

                                    <button
                                        type="button"
                                        class="button button-outline practice-action"
                                        disabled>
                                        Tidak Tersedia
                                    </button>

                                @elseif (
                                    !$assessmentPractice->start_at ||
                                    !$assessmentPractice->deadline_at
                                )

                                    <span class="practice-status unavailable">
                                        Jadwal penilaian belum diatur
                                    </span>

                                    <button
                                        type="button"
                                        class="button button-outline practice-action"
                                        disabled>
                                        Belum Tersedia
                                    </button>

                                @elseif (now()->lt($assessmentPractice->start_at))

                                    <span class="practice-status upcoming">
                                        Penilaian belum dimulai
                                    </span>

                                    <button
                                        type="button"
                                        class="button button-outline practice-action"
                                        disabled>
                                        Belum Dimulai
                                    </button>

                                @elseif (now()->greaterThanOrEqualTo($assessmentPractice->deadline_at))

                                    <span class="practice-status unavailable">
                                        Waktu penilaian sudah berakhir
                                    </span>

                                    <button
                                        type="button"
                                        class="button button-outline practice-action"
                                        disabled>
                                        Waktu Berakhir
                                    </button>

                                @else

                                    <span class="practice-status unavailable">
                                        Penilaian belum tersedia
                                    </span>

                                    <button
                                        type="button"
                                        class="button button-outline practice-action"
                                        disabled>
                                        Tidak Tersedia
                                    </button>

                                @endif

                            </div>

                        @else

                            <div class="module-practice-info">
                                <span>Jadwal</span>
                                <strong>Belum tersedia</strong>
                            </div>

                            <div class="module-practice-footer">

                                <span class="practice-status unavailable">
                                    Latihan penilaian belum tersedia
                                </span>

                                <button
                                    type="button"
                                    class="button button-outline practice-action"
                                    disabled>
                                    Belum Tersedia
                                </button>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </section>

    </main>

</body>

</html>
