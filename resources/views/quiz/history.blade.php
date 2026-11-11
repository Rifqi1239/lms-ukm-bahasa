
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Latihan - LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .history-container {
            max-width: 1200px;
            margin: 36px auto;
            padding: 0 24px;
        }

        .history-header {
            margin-bottom: 28px;
        }

        .history-header h1 {
            margin: 0 0 10px;
            font-size: 28px;
            font-weight: 700;
            color: #18202e;
        }

        .history-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }

        .history-card {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            overflow: hidden;
        }

        .history-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .history-table thead {
            background: #f3f4f6;
        }

        .history-table th {
            padding: 15px 20px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
            text-align: left;
            white-space: nowrap;
        }

        .history-table td {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 13px;
            vertical-align: middle;
        }

        .history-module-name {
            color: #18202e;
            font-weight: 600;
        }

        .history-score {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: 6px 10px;
            border-radius: 6px;
            background: #f3f4f6;
            color: #374151;
            font-weight: 700;
        }

        .history-correct-count {
            white-space: nowrap;
        }

        .history-view-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            border: 1px solid #374151;
            border-radius: 6px;
            background: white;
            color: #374151;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: .2s ease;
        }

        .history-view-button:hover {
            background: #374151;
            color: white;
        }

        .history-empty {
            padding: 60px 24px;
            text-align: center;
        }

        .history-empty h2 {
            margin: 0 0 10px;
            font-size: 20px;
            color: #18202e;
        }

        .history-empty p {
            margin: 0 0 24px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }

        .history-empty-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border-radius: 6px;
            background: #374151;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .history-pagination {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .history-pagination nav {
            display: flex;
            justify-content: center;
        }

        .history-pagination svg {
            width: 18px;
            height: 18px;
        }

        @media (max-width: 700px) {
            .history-container {
                margin: 24px auto;
                padding: 0 16px;
            }

            .history-header h1 {
                font-size: 24px;
            }

            .history-table th,
            .history-table td {
                padding: 13px 15px;
            }

            .history-empty {
                padding: 40px 20px;
            }
        }
    </style>
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
