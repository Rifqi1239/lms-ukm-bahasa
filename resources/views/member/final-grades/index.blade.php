
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penilaian Akhir - UKM Bahasa</title>

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

    <main class="final-grade-container">

        <!-- Judul halaman -->
        <section class="final-grade-welcome">
            <p class="eyebrow">HASIL PEMBELAJARAN</p>

            <h1>Penilaian Akhir</h1>

            <p>
                Lihat hasil akhir pembelajaran berdasarkan nilai latihan
                dari seluruh modul yang tersedia.
            </p>
        </section>

        @if (!$record)

            <!-- Belum ada penilaian -->
            <section class="final-grade-empty">

                <div class="final-grade-empty-icon">📊</div>

                <h2>Penilaian Belum Tersedia</h2>

                <p>
                    Nilai akhir kamu belum disetujui oleh pengajar.
                    Silakan periksa kembali setelah pengajar menyelesaikan
                    proses penilaian.
                </p>

                <a
                    href="{{ route('modules.index') }}"
                    class="button button-primary">
                    Lihat Modul
                </a>

            </section>

        @else

            @php
                $isPassed = (float) $record->final_score >= (float) $record->passing_score;
            @endphp

            <!-- Ringkasan nilai -->
            <section class="final-grade-summary">

                <div class="final-grade-main">

                    <span class="grade-label">Nilai Akhir</span>

                    <div class="grade-number">
                        {{ number_format((float) $record->final_score, 2, ',', '.') }}
                    </div>

                    <span class="grade-status {{ $isPassed ? 'passed' : 'not-passed' }}">
                        {{ $isPassed ? 'Memenuhi Nilai Kelulusan' : 'Belum Memenuhi Nilai Kelulusan' }}
                    </span>

                </div>

                <div class="final-grade-details">

                    <div class="grade-detail-item">
                        <span>Batas Kelulusan</span>
                        <strong>
                            {{ number_format((float) $record->passing_score, 2, ',', '.') }}
                        </strong>
                    </div>

                    <div class="grade-detail-item">
                        <span>Jumlah Modul Dinilai</span>
                        <strong>{{ $record->total_modules }} Modul</strong>
                    </div>

                    <div class="grade-detail-item">
                        <span>Tanggal Persetujuan</span>
                        <strong>
                            {{ $record->approved_at?->format('d M Y') ?? '-' }}
                        </strong>
                    </div>

                </div>

            </section>

            <!-- Rincian nilai modul -->
            <section class="final-grade-section">

                <div class="section-heading">
                    <div>
                        <h2>Rincian Nilai Modul</h2>
                        <p>
                            Nilai terbaik dari latihan setiap modul
                            yang memiliki soal.
                        </p>
                    </div>
                </div>

                @if (count($moduleScores) > 0)

                    <div class="module-score-list">

                        @foreach ($moduleScores as $item)

                            <article class="module-score-item">

                                <div class="module-score-info">

                                    <strong>
                                        {{ $item['module_title'] ?? 'Modul' }}
                                    </strong>

                                    <span>
                                        {{ !empty($item['has_attempt'])
                                            ? 'Latihan telah dikerjakan'
                                            : 'Belum mengerjakan latihan' }}
                                    </span>

                                </div>

                                <div class="module-score-value">
                                    {{ number_format((float) ($item['score'] ?? 0), 2, ',', '.') }}
                                </div>

                            </article>

                        @endforeach

                    </div>

                @else

                    <p>Belum ada rincian nilai modul.</p>

                @endif

            </section>

            <!-- Informasi -->
            <section class="final-grade-note">

                <strong>Informasi Penilaian</strong>

                <p>
                    Nilai akhir merupakan rata-rata nilai terbaik dari
                    seluruh modul yang memiliki soal latihan. Nilai ini
                    telah diperiksa dan disetujui oleh pengajar.
                </p>

            </section>

        @endif

        <footer class="dashboard-footer">
            <p>UKM Bahasa · Sistem Pembelajaran Bahasa</p>
        </footer>

    </main>

</body>

</html>
