@extends('layouts.admin')

@section('title', 'Kelola Soal | LMS UKM Bahasa')

@section('breadcrumb', 'Halaman / Soal Latihan')

@section('page-title', 'Kelola Soal')

@section('content')

{{-- PESAN BERHASIL --}}
@if (session('success'))
<div class="question-alert success">
    {{ session('success') }}
</div>
@endif

{{-- PESAN GAGAL --}}
@if (session('error'))
<div class="question-alert error">
    {{ session('error') }}
</div>
@endif

{{-- PESAN VALIDASI --}}
@if ($errors->any())
<div class="question-alert error">
    <strong>Data belum bisa disimpan.</strong>

    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<section class="admin-dashboard-section">

    <div class="question-workspace">

        {{-- PANEL KIRI: DAFTAR SOAL --}}
        <section class="question-panel">

            <div class="question-panel-header">

                <h2>
                    Daftar Soal ({{ $questions->total() }})
                </h2>

                {{-- FILTER MODUL --}}
                <form
                    action="{{ route('admin.questions.index') }}"
                    method="GET">

                    <select
                        name="module_id"
                        class="question-filter"
                        aria-label="Filter modul"
                        onchange="this.form.submit()">

                        <option value="">
                            Semua Modul
                        </option>

                        @foreach ($modules as $module)
                        <option
                            value="{{ $module->id }}"
                            @selected((string) $selectedModuleId===(string) $module->id)
                            >
                            {{ $module->title }}
                        </option>
                        @endforeach

                    </select>

                </form>

            </div>

            {{-- DAFTAR PERTANYAAN --}}
            @forelse ($questions as $index => $question)

            <article class="question-list-item
                        {{ $selectedQuestion && $selectedQuestion->id === $question->id ? 'selected' : '' }}">

                <p class="question-list-text">

                    <strong>
                        {{ $questions->firstItem() + $index }}.
                    </strong>

                    {{ \Illuminate\Support\Str::limit($question->question_text, 100) }}

                </p>

                <div class="question-list-info">

                    <small>
                        Modul: {{ $question->module?->title ?? '-' }}
                    </small>

                    <small>
                        Jenis latihan:
                        @if ($question->practice?->type === 'assessment')
                        <strong>Latihan Penilaian</strong>
                        @else
                        <strong>Latihan Mandiri</strong>
                        @endif
                    </small>

                </div>

                <div class="question-actions">

                    {{-- UBAH SOAL --}}
                    <a href="{{ route('admin.questions.index', array_filter([
                                'module_id' => $selectedModuleId,
                                'edit' => $question->id,
                            ], fn ($value) => $value !== null && $value !== '')) }}">
                        Ubah
                    </a>

                    {{-- HAPUS SOAL --}}
                    <form
                        action="{{ route('admin.questions.destroy', $question) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus soal ini?')">
                        @csrf
                        @method('DELETE')

                        <input
                            type="hidden"
                            name="return_module_id"
                            value="{{ $selectedModuleId ?? '' }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit">
                            Hapus
                        </button>
                    </form>

                </div>

            </article>

            @empty

            <div class="question-empty">
                Belum ada soal untuk modul ini.
                <br>
                Klik <strong>+ Soal baru</strong> untuk mulai.
            </div>

            @endforelse

            {{-- PAGINATION --}}
            <div class="question-pagination">
                {{ $questions->links() }}
            </div>

            {{-- TOMBOL SOAL BARU --}}
            <div class="question-add-new-wrapper">

                <a
                    href="{{ route('admin.questions.index', $selectedModuleId ? ['module_id' => $selectedModuleId] : []) }}"
                    class="question-button secondary question-add-new-button">
                    + Soal baru
                </a>

            </div>

        </section>

        {{-- PANEL KANAN: TAMBAH / UBAH SOAL --}}
        <section class="question-panel">

            <div class="question-panel-header">

                <h2>
                    {{ $selectedQuestion ? 'Ubah Soal' : 'Tambah Soal' }}
                </h2>

                <p class="question-panel-description">
                    Lengkapi pertanyaan dan pilihan jawaban.
                </p>

            </div>

            <div class="question-panel-body">

                @php
                $editingOptions = $selectedQuestion
                ? $selectedQuestion->options->keyBy('label')
                : collect();

                $currentQuestionText = old(
                'question_text',
                $selectedQuestion?->question_text ?? ''
                );

                $currentExplanation = old(
                'explanation',
                $selectedQuestion?->explanation ?? ''
                );

                $currentCorrectOption = old(
                'correct_option',
                $editingOptions->firstWhere('is_correct', true)?->label
                );

                $optionEValue = old(
                'options.E',
                $editingOptions->get('E')?->option_text ?? ''
                );

                $showOptionE = $optionEValue !== ''
                || $currentCorrectOption === 'E';

                $formModuleId = old(
                'module_id',
                $selectedQuestion?->module_id ?? $selectedModuleId
                );

                $currentPracticeType = old(
                'practice_type',
                $selectedQuestion?->practice?->type ?? 'independent'
                );

                $currentStartAt = old(
                'start_at',
                $selectedQuestion?->practice?->start_at?->format('Y-m-d\TH:i') ?? ''
                );

                $currentDeadlineAt = old(
                'deadline_at',
                $selectedQuestion?->practice?->deadline_at?->format('Y-m-d\TH:i') ?? ''
                );
                @endphp

                {{-- FORM TAMBAH / UBAH --}}
                <form
                    action="{{ $selectedQuestion
                            ? route('admin.questions.update', $selectedQuestion)
                            : route('admin.questions.store') }}"
                    method="POST">

                    @csrf

                    @if ($selectedQuestion)
                    @method('PUT')
                    @endif

                    @if ($formModuleId)
                    <input
                        type="hidden"
                        name="return_module_id"
                        value="{{ $formModuleId }}">
                    @endif

                    {{-- PILIH MODUL --}}
                    <div class="question-form-group">

                        @if ($formModuleId)

                        <input
                            type="hidden"
                            name="module_id"
                            value="{{ $formModuleId }}">

                        <label>Modul</label>

                        <input
                            type="text"
                            class="question-input"
                            value="{{ $modules->firstWhere('id', (int) $formModuleId)?->title ?? $selectedQuestion?->module?->title }}"
                            disabled>

                        @else

                        <label for="module_id">
                            Modul
                        </label>

                        <select
                            id="module_id"
                            name="module_id"
                            class="question-select"
                            required>

                            <option value="">
                                Pilih modul
                            </option>

                            @foreach ($modules as $module)
                            <option
                                value="{{ $module->id }}"
                                @selected((string) $formModuleId===(string) $module->id)
                                >
                                {{ $module->title }}
                            </option>
                            @endforeach

                        </select>

                        @endif

                    </div>

                    {{-- JENIS LATIHAN --}}
                    <div class="question-form-group">

                        <label for="practice_type">
                            Jenis Latihan
                        </label>

                        <select
                            id="practice_type"
                            name="practice_type"
                            class="question-select"
                            required>

                            <option
                                value="independent"
                                @selected($currentPracticeType==='independent' )>
                                Latihan Mandiri
                            </option>

                            <option
                                value="assessment"
                                @selected($currentPracticeType==='assessment' )>
                                Latihan Penilaian
                            </option>

                        </select>

                        <small>
                            Latihan mandiri dapat dikerjakan kapan saja.
                            Latihan penilaian memiliki jadwal pengerjaan.
                        </small>

                    </div>

                    {{-- JADWAL LATIHAN PENILAIAN --}}
                    <div
                        id="assessment-schedule"
                        style="{{ $currentPracticeType === 'assessment' ? '' : 'display: none;' }}">

                        <div class="question-form-group">

                            <label for="start_at">
                                Tanggal dan Waktu Mulai
                            </label>

                            <input
                                type="datetime-local"
                                id="start_at"
                                name="start_at"
                                class="question-input"
                                value="{{ $currentStartAt }}"
                                {{ $currentPracticeType === 'assessment' ? 'required' : '' }}>

                        </div>

                        <div class="question-form-group">

                            <label for="deadline_at">
                                Deadline Pengerjaan
                            </label>

                            <input
                                type="datetime-local"
                                id="deadline_at"
                                name="deadline_at"
                                class="question-input"
                                value="{{ $currentDeadlineAt }}"
                                {{ $currentPracticeType === 'assessment' ? 'required' : '' }}>

                            <small>
                                Anggota hanya dapat memulai dan menyelesaikan
                                latihan penilaian sebelum deadline.
                            </small>

                        </div>

                    </div>

                    {{-- PERTANYAAN --}}
                    <div class="question-form-group">

                        <label for="question_text">
                            Pertanyaan
                        </label>

                        <textarea
                            id="question_text"
                            name="question_text"
                            class="question-textarea"
                            rows="4"
                            required
                            placeholder="Tulis pertanyaan...">{{ $currentQuestionText }}</textarea>

                    </div>

                    {{-- PILIHAN JAWABAN --}}
                    <div class="question-form-group">

                        <label>Opsi Jawaban</label>

                        {{-- OPSI A-D --}}
                        @foreach (['A', 'B', 'C', 'D'] as $label)

                        @php
                        $optionValue = old(
                        'options.'.$label,
                        $editingOptions->get($label)?->option_text ?? ''
                        );
                        @endphp

                        <div class="question-option-row">

                            <div>

                                <label
                                    for="option_{{ $label }}"
                                    class="question-option-label">
                                    Opsi {{ $label }}
                                </label>

                                <input
                                    id="option_{{ $label }}"
                                    type="text"
                                    name="options[{{ $label }}]"
                                    value="{{ $optionValue }}"
                                    class="question-input"
                                    maxlength="1000"
                                    required
                                    placeholder="Masukkan opsi {{ $label }}">

                            </div>

                            <label class="question-correct-label">

                                <input
                                    type="radio"
                                    name="correct_option"
                                    value="{{ $label }}"
                                    @checked($currentCorrectOption===$label)
                                    required>

                                Benar

                            </label>

                        </div>

                        @endforeach

                        {{-- OPSI E --}}
                        <div
                            id="option-e-container"
                            class="{{ $showOptionE ? '' : 'question-option-hidden' }}">

                            <div class="question-option-row">

                                <div>

                                    <label
                                        for="option_E"
                                        class="question-option-label">
                                        Opsi E
                                    </label>

                                    <input
                                        id="option_E"
                                        type="text"
                                        name="options[E]"
                                        value="{{ $optionEValue }}"
                                        class="question-input"
                                        maxlength="1000"
                                        {{ $showOptionE ? 'required' : 'disabled' }}
                                        placeholder="Masukkan opsi E">

                                </div>

                                <label class="question-correct-label">

                                    <input
                                        type="radio"
                                        name="correct_option"
                                        value="E"
                                        @checked($currentCorrectOption==='E' )
                                        {{ $showOptionE ? 'required' : '' }}>

                                    Benar

                                </label>

                            </div>

                        </div>

                        {{-- TOMBOL TAMBAH OPSI E --}}
                        <button
                            type="button"
                            id="add-option-button"
                            class="question-button question-add-option-button"
                            {{ $showOptionE ? 'hidden disabled' : '' }}>
                            + Tambah opsi (maks. 5)
                        </button>

                    </div>

                    {{-- PEMBAHASAN --}}
                    <div class="question-form-group">

                        <label for="explanation">
                            Pembahasan (opsional)
                        </label>

                        <textarea
                            id="explanation"
                            name="explanation"
                            class="question-textarea"
                            rows="3"
                            placeholder="Jelaskan pembahasan jawaban...">{{ $currentExplanation }}</textarea>

                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="question-form-footer">

                        <a
                            href="{{ route('admin.questions.index', $selectedModuleId ? ['module_id' => $selectedModuleId] : []) }}"
                            class="question-button secondary">
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="question-button">
                            {{ $selectedQuestion ? 'Simpan Perubahan' : 'Simpan Soal' }}
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </div>

</section>

@endsection

{{-- JAVASCRIPT --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ==========================================
        // TAMBAH OPSI E
        // ==========================================

        const addButton = document.getElementById('add-option-button');
        const optionContainer = document.getElementById('option-e-container');
        const optionEInput = document.getElementById('option_E');

        if (addButton && optionContainer && optionEInput) {

            addButton.addEventListener('click', function() {

                optionContainer.classList.remove('question-option-hidden');

                optionEInput.disabled = false;
                optionEInput.required = true;

                addButton.hidden = true;
                addButton.disabled = true;

            });

        }

        // ==========================================
        // JENIS LATIHAN DAN JADWAL
        // ==========================================

        const practiceType = document.getElementById('practice_type');
        const scheduleContainer = document.getElementById('assessment-schedule');

        const startInput = document.getElementById('start_at');
        const deadlineInput = document.getElementById('deadline_at');

        function toggleAssessmentSchedule() {

            if (!practiceType || !scheduleContainer) {
                return;
            }

            const isAssessment = practiceType.value === 'assessment';

            scheduleContainer.style.display = isAssessment ? '' : 'none';

            startInput.required = isAssessment;
            deadlineInput.required = isAssessment;

            if (!isAssessment) {
                startInput.required = false;
                deadlineInput.required = false;
            }

        }

        if (practiceType) {
            practiceType.addEventListener('change', toggleAssessmentSchedule);
            toggleAssessmentSchedule();
        }

        // ==========================================
        // VALIDASI DEADLINE DI FORM
        // ==========================================

        if (startInput && deadlineInput) {

            function validateDeadline() {

                if (
                    startInput.value &&
                    deadlineInput.value
                ) {
                    deadlineInput.min = startInput.value;
                } else {
                    deadlineInput.removeAttribute('min');
                }

            }

            startInput.addEventListener('change', validateDeadline);

            validateDeadline();

        }

    });
</script>
@endpush