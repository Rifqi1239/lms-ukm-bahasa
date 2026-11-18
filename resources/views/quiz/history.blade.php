
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Latihan - LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="lms-page">

    {{-- NAVBAR --}}
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

    <main class="history-container">

        {{-- HEADER HALAMAN --}}
        <header class="history-header">

            <h1>Riwayat Latihan</h1>

            <p>
                Lihat kembali latihan yang sudah kamu kerjakan,
                nilai yang diperoleh, dan pembahasan setiap soal.
            </p>

        </header>

        {{-- TABEL RIWAYAT --}}
        <section class="history-card">

            @if ($attempts->count() > 0)

                <div class="history-table-wrapper">

                    <table class="history-table">

                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Modul</th>
                                <th>Nilai</th>
                                <th>Jawaban Benar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($attempts as $attempt)

                                @php
                                    $total = (int) $attempt->total_questions;

                                    $percentage = $total > 0
                                        ? (int) round(
                                            ($attempt->score / $total) * 100
                                        )
                                        : 0;
                                @endphp

                                <tr>

                                    <td>
                                        {{ $attempt->completed_at->translatedFormat('d M Y') }}
                                    </td>

                                    <td>
                                        <span class="history-module-name">
                                            {{ $attempt->module?->title ?? 'Modul tidak tersedia' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="history-score">
                                            {{ $percentage }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="history-correct-count">
                                            {{ $attempt->score }}
                                            /
                                            {{ $total }}
                                        </span>
                                    </td>

                                    <td>
                                        <a
                                            href="{{ route('quiz.result', $attempt) }}"
                                            class="history-view-button">
                                            Lihat
                                        </a>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- PAGINATION --}}
                @if ($attempts->hasPages())

                    <div class="history-pagination">
                        {{ $attempts->links() }}
                    </div>

                @endif

            @else

                <div class="history-empty">

                    <h2>Belum Ada Riwayat Latihan</h2>

                    <p>
                        Kamu belum menyelesaikan latihan apa pun.
                        Mulai belajar dan kerjakan latihan dari modul yang tersedia.
                    </p>

                    <a
                        href="{{ route('modules.index') }}"
                        class="history-empty-button">
                        Lihat Modul
                    </a>

                </div>

            @endif

        </section>

    </main>

</body>

</html>
