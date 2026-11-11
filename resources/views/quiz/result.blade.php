<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hasil Latihan - {{ $module->title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --result-primary: #374151;
            --result-primary-hover: #273244;
            --result-border: #cbd0d8;
            --result-background: #f8f9fb;
            --result-text: #18202e;
            --result-muted: #7b8493;
            --result-correct: #bbf7d0;
            --result-correct-text: #166534;
            --result-wrong: #fecaca;
            --result-wrong-text: #991b1b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--result-background);
            color: var(--result-text);
            font-family: Arial, sans-serif;
        }

        .result-container {
            width: min(1200px, 100%);
            margin: 32px auto;
            padding: 0 24px;
        }

        /* RINGKASAN NILAI */

        .result-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 28px;
            background: white;
            border: 1px solid var(--result-border);
            border-radius: 8px;
        }

        .result-score-section {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .result-score {
            font-size: 56px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: -2px;
        }

        .result-summary-info h1 {
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 700;
        }

        .result-summary-info p {
            margin: 0;
            color: var(--result-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .result-summary-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 220px;
            flex-shrink: 0;
        }

        .result-button {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 42px;
            padding: 10px 18px;
            border: 1px solid var(--result-primary);
            border-radius: 6px;
            background: white;
            color: var(--result-primary);
            text-decoration: none;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s ease;
        }

        .result-button:hover {
            background: #f1f3f6;
        }

        .result-button-primary {
            background: var(--result-primary);
            color: white;
        }

        .result-button-primary:hover {
            background: var(--result-primary-hover);
            color: white;
        }

        /* PEMBAHASAN */

        .result-discussion {
            margin-top: 28px;
        }

        .result-discussion-title {
            margin: 0 0 14px;
            font-size: 18px;
            font-weight: 700;
        }

        .result-question-card {
            margin-bottom: 12px;
            padding: 18px 20px;
            background: white;
            border: 1px solid var(--result-border);
            border-radius: 8px;
        }

        .result-question-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
        }

        .result-question-number {
            font-size: 14px;
            font-weight: 700;
        }

        .result-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .result-status-correct {
            background: var(--result-correct);
            color: var(--result-correct-text);
        }

        .result-status-wrong {
            background: var(--result-wrong);
            color: var(--result-wrong-text);
        }

        .result-question-text {
            margin: 0 0 14px;
            font-size: 14px;
            line-height: 1.7;
            font-weight: 500;
        }

        .result-answer-details {
            display: grid;
            gap: 8px;
            color: var(--result-muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .result-answer-details strong {
            color: var(--result-text);
        }

        .result-explanation {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            color: #626d7c;
            font-size: 12px;
            line-height: 1.7;
        }

        .result-explanation strong {
            color: var(--result-text);
        }

        .result-empty {
            padding: 24px;
            background: white;
            border: 1px solid var(--result-border);
            border-radius: 8px;
            color: var(--result-muted);
            text-align: center;
        }

        @media (max-width: 700px) {
            .result-container {
                margin: 20px auto;
                padding: 0 16px;
            }

            .result-summary {
                align-items: stretch;
                flex-direction: column;
                padding: 22px;
            }

            .result-score-section {
                align-items: flex-start;
                gap: 18px;
            }

            .result-score {
                font-size: 44px;
            }

            .result-summary-info h1 {
                font-size: 17px;
            }

            .result-summary-actions {
                width: 100%;
            }

            .result-question-card {
                padding: 16px;
            }
        }
    </style>
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