
@extends('layouts.admin')

@section('page_title', 'Kelola Modul')
@section('breadcrumb', 'Kelola Modul / Tambah Modul')

@push('styles')
<style>
    .module-form-content {
        padding: 24px;
        background: #fff;
    }

    .module-form-heading {
        margin-bottom: 18px;
    }

    .module-form-heading h1 {
        margin: 0;
        color: #1f2937;
        font-size: 25px;
        font-weight: 700;
    }

    .module-form-card {
        padding: 24px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
    }

    .module-form-group {
        margin-bottom: 18px;
    }

    .module-form-group label,
    .module-cover-label {
        display: block;
        margin-bottom: 6px;
        color: #4b5563;
        font-size: 12px;
    }

    .module-form-control {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        color: #1f2937;
        font: inherit;
        font-size: 13px;
    }

    .module-form-control:focus {
        outline: none;
        border-color: #64748b;
        box-shadow: 0 0 0 2px rgb(100 116 139 / 12%);
    }

    .module-form-control::placeholder {
        color: #9ca3af;
    }

    textarea.module-form-control {
        min-height: 100px;
        resize: vertical;
    }

    .module-language-select {
        max-width: 360px;
    }

    .module-upload-box {
        padding: 22px 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        background: #f9fafb;
        text-align: center;
    }

    .module-upload-box p {
        margin: 0 0 10px;
        color: #6b7280;
        font-size: 13px;
    }

    .module-file-input {
        display: block;
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        color: #4b5563;
        font-size: 12px;
    }

    .module-cover-options {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-top: 10px;
    }

    .module-cover-options label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0;
        color: #4b5563;
        font-size: 12px;
        cursor: pointer;
    }

    .module-cover-options input {
        accent-color: #374151;
    }

    .module-cover-upload {
        margin-top: 14px;
    }

    .module-cover-upload[hidden] {
        display: none;
    }

    .module-form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
    }

    .module-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        padding: 10px 16px;
        border: 1px solid #9ca3af;
        border-radius: 6px;
        background: #fff;
        color: #374151;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
    }

    .module-button-primary {
        border-color: #374151;
        background: #374151;
        color: #fff;
    }

    .module-button:hover {
        opacity: .9;
    }

    .module-form-error {
        margin-top: 5px;
        color: #dc2626;
        font-size: 12px;
    }

    .module-form-alert {
        margin-bottom: 16px;
        padding: 12px 15px;
        border-radius: 6px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 13px;
    }

    @media (max-width: 640px) {
        .module-form-content {
            padding: 16px;
        }

        .module-form-card {
            padding: 16px;
        }

        .module-cover-options {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>
@endpush

@section('content')

<section class="module-form-content">

    <div class="module-form-heading">
        <h1>Unggah Modul Baru</h1>
    </div>

    @if ($errors->any())
        <div class="module-form-alert">
            <strong>Periksa kembali isian formulir.</strong>

            <ul style="margin-bottom:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.modules.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="module-form-card">

        @csrf

        <!-- JUDUL MODUL -->
        <div class="module-form-group">
            <label for="title">Judul modul</label>

            <input
                type="text"
                id="title"
                name="title"
                class="module-form-control"
                placeholder="Judul modul"
                value="{{ old('title') }}"
                required
                maxlength="255">

            @error('title')
                <div class="module-form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- SINOPSIS -->
        <div class="module-form-group">
            <label for="description">Sinopsis</label>

            <textarea
                id="description"
                name="description"
                class="module-form-control"
                placeholder="Ringkasan singkat isi modul..."
                rows="4">{{ old('description') }}</textarea>

            @error('description')
                <div class="module-form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- KELOMPOK BAHASA -->
        <div class="module-form-group">
            <label for="language_group_id">Kelompok bahasa</label>

            <select
                id="language_group_id"
                name="language_group_id"
                class="module-form-control module-language-select"
                required>

                <option value="">Pilih kelompok bahasa</option>

                @foreach ($languageGroups as $group)
                    <option
                        value="{{ $group->id }}"
                        @selected(old('language_group_id') == $group->id)>

                        {{ $group->name }}

                        @if ($group->code)
                            ({{ $group->code }})
                        @endif
                    </option>
                @endforeach

            </select>

            @error('language_group_id')
                <div class="module-form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- FILE PDF -->
        <div class="module-form-group">
            <label for="pdf_file">File modul (PDF)</label>

            <div class="module-upload-box">
                <p>Tarik file PDF ke sini atau pilih file</p>

                <input
                    type="file"
                    id="pdf_file"
                    name="pdf_file"
                    class="module-file-input"
                    accept=".pdf,application/pdf"
                    required>
            </div>

            @error('pdf_file')
                <div class="module-form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- PILIHAN SAMPUL -->
        <div class="module-form-group">

            <span class="module-cover-label">Sampul</span>

            <div class="module-cover-options">

                <label>
                    <input
                        type="radio"
                        name="cover_mode"
                        value="automatic"
                        @checked(old('cover_mode', 'automatic') === 'automatic')>

                    Otomatis dari halaman pertama PDF
                </label>

                <label>
                    <input
                        type="radio"
                        name="cover_mode"
                        value="upload"
                        @checked(old('cover_mode') === 'upload')>

                    Unggah sampul sendiri
                </label>

            </div>

            <div
                id="cover-upload-container"
                class="module-cover-upload"
                @if (old('cover_mode', 'automatic') !== 'upload') hidden @endif>

                <label for="cover_image">Pilih gambar sampul</label>

                <input
                    type="file"
                    id="cover_image"
                    name="cover_image"
                    class="module-file-input"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    @required(old('cover_mode') === 'upload')>

                <small style="display:block;margin-top:6px;color:#6b7280;">
                    Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                </small>

                @error('cover_image')
                    <div class="module-form-error">{{ $message }}</div>
                @enderror

            </div>

        </div>

        <!-- TOMBOL -->
        <div class="module-form-actions">

            <a
                href="{{ route('admin.modules.index') }}"
                class="module-button">
                Batal
            </a>

            <button
                type="submit"
                class="module-button module-button-primary">
                Simpan Modul
            </button>

        </div>

    </form>

</section>

@endsection

@push('scripts')
<script>
    const coverRadios = document.querySelectorAll(
        'input[name="cover_mode"]'
    );

    const coverContainer = document.getElementById(
        'cover-upload-container'
    );

    const coverInput = document.getElementById('cover_image');

    function updateCoverInput() {
        const selected = document.querySelector(
            'input[name="cover_mode"]:checked'
        );

        const uploadSelected = selected && selected.value === 'upload';

        coverContainer.hidden = !uploadSelected;
        coverInput.required = Boolean(uploadSelected);

        if (!uploadSelected) {
            coverInput.value = '';
        }
    }

    coverRadios.forEach(function(radio) {
        radio.addEventListener('change', updateCoverInput);
    });

    updateCoverInput();
</script>
@endpush
