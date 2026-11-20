<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hasil Latihan - {{ $module->title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

   
</head>

<body>

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
                class="nav-link {{ request()->routeIs('quiz.history', 'quiz.result') ? 'active' : '' }}">
                Riwayat Latihan
            </a>

        </div>

        <div class="nav-user">

            @if (auth()->user()->languageGroup)
            <span class="nav-group">
                {{ auth()->user()->languageGroup->name }}
            </span>
            @endif

            <div class="nav-user-info">
                <span class="nav-user-name">
                    {{ auth()->user()->name }}
                </span>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit" class="nav-logout">
                    Keluar
                </button>
            </form>

        </div>

    </nav>


    <main class="result-container">

        {{-- RINGKASAN HASIL --}}
        <section class="result-summary">

            <div class="result-score-section">

                <div class="result-score">
                    {{ $scorePercentage }}
                </div>

                <div class="result-summary-info">

                    <h1>
                        {{ $correctCount }} dari {{ $totalQuestions }} soal benar
                    </h1>

                    <p>
                        Latihan: {{ $module->title }}
                        @if ($attempt->completed_at)
                        · {{ $attempt->completed_at->translatedFormat('d M Y') }}
                        @endif
                    </p>

                </div>

            </div>

            <div class="result-summary-actions">

                <a
                    href="{{ route('quiz.start', $module) }}"
                    class="result-button">
                    Ulangi Latihan
                </a>

                <a
                    href="{{ route('modules.show', $module) }}"
                    class="result-button result-button-primary">
                    Kembali ke Modul
                </a>

            </div>

        </section>


        {{-- PEMBAHASAN SOAL --}}
        <section class="result-discussion">

            <h2 class="result-discussion-title">
                Pembahasan
            </h2>

            @forelse ($questions as $index => $question)

            @php
            $answer = $answers->get($question->id);

            $isAnswered = $answer
            && $answer->selected_option_id !== null;

            $isCorrect = $isAnswered
            && (bool) $answer->is_correct;

            $selectedOption = $isAnswered
            ? $question->options->firstWhere(
            'id',
            (int) $answer->selected_option_id
            )
            : null;

            $correctOption = $question->options->first(
            fn ($option) => (bool) $option->is_correct
            );
            @endphp

            <article class="result-question-card">

                <div class="result-question-header">

                    <span class="result-question-number">
                        Soal {{ $index + 1 }}
                    </span>

                    @if ($isCorrect)

                    <span class="result-status result-status-correct">
                        Benar
                    </span>

                    @else

                    <span class="result-status result-status-wrong">
                        Salah
                    </span>

                    @endif

                </div>

                <p class="result-question-text">
                    {{ $question->question_text }}
                </p>

                <div class="result-answer-details">

                    <div>
                        <strong>Jawaban Anda:</strong>

                        @if ($selectedOption)
                        {{ $selectedOption->label }}.
                        {{ $selectedOption->option_text }}
                        @else
                        Tidak dijawab
                        @endif
                    </div>

                    <div>
                        <strong>Jawaban benar:</strong>

                        @if ($correctOption)
                        {{ $correctOption->label }}.
                        {{ $correctOption->option_text }}
                        @else
                        Belum ditentukan
                        @endif
                    </div>

                </div>

                @if ($question->explanation)

                <div class="result-explanation">

                    <strong>Pembahasan:</strong>

                    {{ $question->explanation }}

                </div>

                @endif

            </article>

            @empty

            <div class="result-empty">
                Tidak ada soal yang dapat ditampilkan.
            </div>

            @endforelse

        </section>

    </main>

</body>

</html>
