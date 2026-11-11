
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Latihan - {{ $module->title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            margin: 0;
            background: #f5f7fb;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .quiz-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .quiz-container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 20px;
        }

        .quiz-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(250px, 1fr);
            gap: 24px;
        }

        .quiz-card, .navigation-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 28px;
        }

        .question-text {
            font-size: 20px;
            line-height: 1.7;
            margin: 24px 0;
        }

        .option-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: .2s;
        }

        .option-label:hover {
            border-color: #2563eb;
            background: #f8faff;
        }

        .option-label.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .question-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .question-number {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            color: #374151;
            text-decoration: none;
            font-weight: 600;
        }

        .question-number.answered {
            background: #dcfce7;
            border-color: #86efac;
        }

        .question-number.active {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }

        .quiz-button {
            display: inline-block;
            padding: 11px 18px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .button-primary {
            background: #2563eb;
            color: white;
        }

        .button-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .button-danger {
            background: #dc2626;
            color: white;
        }

        .quiz-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 28px;
        }

        .save-status {
            font-size: 13px;
            color: #6b7280;
            margin-top: 12px;
            min-height: 20px;
        }

        @media (max-width: 760px) {
            .quiz-layout {
                grid-template-columns: 1fr;
            }

            .quiz-header {
                padding: 16px 20px;
                align-items: flex-start;
                flex-direction: column;
            }

            .quiz-card, .navigation-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<header class="quiz-header">
    <div>
        <strong>UKM Bahasa</strong>
        <div style="color:#6b7280;font-size:14px;margin-top:5px">
            {{ $module->title }}
        </div>
    </div>

    <a href="{{ route('modules.read', $module) }}"
       class="quiz-button button-secondary">
        Kembali ke Modul
    </a>
</header>

<main class="quiz-container">

    <div style="margin-bottom:24px">
        <h1 style="font-size:28px;font-weight:700;margin:0 0 8px">
            Latihan Modul
        </h1>

        <p style="color:#6b7280;margin:0">
            Kerjakan soal sesuai pemahamanmu. Progres jawaban akan disimpan.
        </p>
    </div>

    <div class="quiz-layout">

        {{-- PANEL SOAL --}}
        <section class="quiz-card">

            @php
                $currentIndex = $questions->search(
                    fn ($question) => $question->id === $currentQuestion->id
                );

                $currentNumber = $currentIndex + 1;
                $totalQuestions = $questions->count();

                $currentAnswer = $answers->get($currentQuestion->id);

                $previousQuestion = $questions->get($currentIndex - 1);
                $nextQuestion = $questions->get($currentIndex + 1);
            @endphp

            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <span style="font-weight:600">
                    Soal {{ $currentNumber }} dari {{ $totalQuestions }}
                </span>

                <span style="color:#6b7280;font-size:14px">
                    {{ $answeredCount }} dari {{ $totalQuestions }} dijawab
                </span>
            </div>

            <div style="height:8px;background:#e5e7eb;border-radius:99px;margin-top:16px;overflow:hidden">
                <svg width="100%" height="8" viewBox="0 0 100 8" preserveAspectRatio="none" style="display:block">
                    <rect x="0" y="0" width="{{ $totalQuestions ? ($answeredCount / $totalQuestions) * 100 : 0 }}" height="8" fill="#2563eb" />
                </svg>
            </div>

            <h2 class="question-text">
                {{ $currentQuestion->question_text }}
            </h2>

            <form id="answer-form"
                  method="POST"
                  action="{{ route('quiz.answer', $attempt) }}">
                @csrf

                <input type="hidden"
                       name="question_id"
                       value="{{ $currentQuestion->id }}">

                @foreach ($currentQuestion->options as $option)
                    <label class="option-label {{ $currentAnswer && (int) $currentAnswer->selected_option_id === (int) $option->id ? 'selected' : '' }}">

                        <input
                            type="radio"
                            name="selected_option_id"
                            value="{{ $option->id }}"
                            {{ $currentAnswer && (int) $currentAnswer->selected_option_id === (int) $option->id ? 'checked' : '' }}
                            style="margin-top:4px"
                        >

                        <span>{{ $option->label }}. {{ $option->option_text }}</span>
                    </label>
                @endforeach

                <p id="save-status" class="save-status" aria-live="polite">
                    @if ($currentAnswer && $currentAnswer->selected_option_id)
                        Jawaban tersimpan.
                    @else
                        Pilih jawaban untuk menyimpannya.
                    @endif
                </p>
            </form>

            <div class="quiz-actions">

                @if ($previousQuestion)
                    <a class="quiz-button button-secondary"
                       href="{{ route('quiz.show', ['attempt' => $attempt->id, 'question' => $previousQuestion->id]) }}">
                        ← Sebelumnya
                    </a>
                @else
                    <span></span>
                @endif

                @if ($nextQuestion)
                    <a class="quiz-button button-primary"
                       href="{{ route('quiz.show', ['attempt' => $attempt->id, 'question' => $nextQuestion->id]) }}">
                        Selanjutnya →
                    </a>
                @endif

            </div>
        </section>

        {{-- NAVIGASI NOMOR SOAL --}}
        <aside class="navigation-card">

            <h2 style="font-size:19px;font-weight:700;margin:0">
                Navigasi Soal
            </h2>

            <p style="color:#6b7280;font-size:14px;margin-top:8px">
                Pilih nomor untuk berpindah ke soal lain.
            </p>

            <div class="question-grid">
                @foreach ($questions as $index => $question)
                    @php
                        $answer = $answers->get($question->id);

                        $isAnswered = $answer
                            && $answer->selected_option_id !== null;

                        $isActive = $question->id === $currentQuestion->id;
                    @endphp

                    <a href="{{ route('quiz.show', ['attempt' => $attempt->id, 'question' => $question->id]) }}"
                       class="question-number {{ $isActive ? 'active' : ($isAnswered ? 'answered' : '') }}"
                       aria-label="Soal {{ $index + 1 }}">
                        {{ $index + 1 }}
                    </a>
                @endforeach
            </div>

            <div style="margin-top:24px;font-size:13px;display:grid;gap:10px">

                <div style="display:flex;align-items:center;gap:9px">
                    <span style="width:14px;height:14px;border-radius:4px;background:#2563eb"></span>
                    Soal aktif
                </div>

                <div style="display:flex;align-items:center;gap:9px">
                    <span style="width:14px;height:14px;border-radius:4px;background:#dcfce7;border:1px solid #86efac"></span>
                    Sudah dijawab
                </div>

                <div style="display:flex;align-items:center;gap:9px">
                    <span style="width:14px;height:14px;border-radius:4px;background:white;border:1px solid #d1d5db"></span>
                    Belum dijawab
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0">

            <p style="font-size:14px;color:#6b7280">
                Pastikan jawabanmu sudah tersimpan sebelum menyelesaikan latihan.
            </p>

            <form method="POST"
                  action="{{ route('quiz.submit', $attempt) }}"
                  onsubmit="return confirm('Apakah kamu yakin ingin menyelesaikan latihan ini? Jawaban tidak dapat diubah setelah dikirim.')">
                @csrf

                <button type="submit"
                        class="quiz-button button-danger"
                        style="width:100%;margin-top:10px">
                    Selesai &amp; Kirim
                </button>
            </form>

        </aside>

    </div>

</main>

<script>
    const answerForm = document.getElementById('answer-form');
    const saveStatus = document.getElementById('save-status');
    const radios = answerForm.querySelectorAll('input[type="radio"]');

    let saving = false;

    radios.forEach((radio) => {
        radio.addEventListener('change', async () => {
            if (saving) return;

            saving = true;
            saveStatus.textContent = 'Menyimpan jawaban...';

            answerForm.querySelectorAll('.option-label').forEach((label) => {
                label.classList.remove('selected');
            });

            radio.closest('.option-label').classList.add('selected');

            try {
                const response = await fetch(answerForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(answerForm)
                });

                if (!response.ok) {
                    throw new Error('Gagal menyimpan jawaban.');
                }

                saveStatus.textContent = 'Jawaban berhasil disimpan.';
            } catch (error) {
                saveStatus.textContent = 'Gagal menyimpan jawaban. Pilih kembali untuk mencoba.';
            } finally {
                saving = false;
            }
        });
    });
</script>

</body>
</html>
