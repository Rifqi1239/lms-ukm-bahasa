
@extends('layouts.admin')

@section('page_title', 'Kelola Modul')
@section('breadcrumb', 'Kelola Modul / Edit Modul')

@section('content')

<section class="module-form-content">

    <div class="module-form-heading">
        <h1>Edit Modul</h1>
    </div>

    <!-- NOTIFIKASI SUKSES -->
    @if (session('success'))
        <div class="module-success-alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- ERROR VALIDASI -->
    @if ($errors->any())
        <div class="module-form-alert">
            <strong>Periksa kembali isian formulir.</strong>

            <ul style="margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FORM EDIT MODUL -->
    <form
        id="module-edit-form"
        action="{{ route('admin.modules.update', $module) }}"
        method="POST"
        enctype="multipart/form-data"
        class="module-form-card"
        data-current-pdf-url="{{ route('admin.modules.pdf', $module) }}"
    >

        @csrf
        @method('PUT')

        <!-- JUDUL MODUL -->
        <div class="module-form-group">

            <label for="title">Judul modul</label>

            <input
                type="text"
                id="title"
                name="title"
                class="module-form-control"
                value="{{ old('title', $module->title) }}"
                placeholder="Judul modul"
                maxlength="255"
                required
            >

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
                rows="4"
            >{{ old('description', $module->description) }}</textarea>

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
                required
            >

                <option value="">Pilih kelompok bahasa</option>

                @foreach ($languageGroups as $group)
                    <option
                        value="{{ $group->id }}"
                        @selected(old('language_group_id', $module->language_group_id) == $group->id)
                    >
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

            <label>File modul saat ini</label>

            @if ($module->pdf_file)
                <p class="module-current-file">
                    PDF tersimpan:

                    <a
                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($module->pdf_file) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Lihat PDF saat ini
                    </a>
                </p>
            @else
                <p class="module-current-file">
                    Belum ada file PDF yang tersimpan.
                </p>
            @endif

            <label for="pdf_file">Ganti file PDF (opsional)</label>

            <div class="module-upload-box">
                <p>Pilih PDF baru jika ingin mengganti file lama</p>

                <input
                    type="file"
                    id="pdf_file"
                    name="pdf_file"
                    class="module-file-input"
                    accept=".pdf,application/pdf"
                >
            </div>

            <small class="module-help-text">
                Kosongkan jika tidak ingin mengganti PDF.
                Format PDF, maksimal 20 MB.
            </small>

            @error('pdf_file')
                <div class="module-form-error">{{ $message }}</div>
            @enderror

        </div>

        <!-- PILIHAN SAMPUL -->
        <div class="module-form-group">

            <span class="module-cover-label">Pilihan sampul</span>

            <div class="module-cover-options">

                <label>
                    <input
                        type="radio"
                        name="cover_mode"
                        value="automatic"
                        @checked(old(
                            'cover_mode',
                            $module->cover_image ? 'upload' : 'automatic'
                        ) === 'automatic')
                    >

                    Otomatis dari halaman pertama PDF
                </label>

                <label>
                    <input
                        type="radio"
                        name="cover_mode"
                        value="upload"
                        @checked(old(
                            'cover_mode',
                            $module->cover_image ? 'upload' : 'automatic'
                        ) === 'upload')
                    >

                    Unggah sampul sendiri
                </label>

            </div>

            <!-- SAMPUL TERSIMPAN -->
            @if ($module->cover_image)

                <div>
                    <p class="module-current-file">
                        Sampul yang tersimpan saat ini:
                    </p>

                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($module->cover_image) }}"
                        alt="Sampul {{ $module->title }}"
                        class="module-current-cover"
                    >
                </div>

            @else

                <p class="module-current-file">
                    Belum ada gambar sampul tersimpan.
                </p>

            @endif

            <!-- UPLOAD SAMPUL BARU -->
            <div
                id="cover-upload-container"
                class="module-cover-upload"
                @if (old(
                    'cover_mode',
                    $module->cover_image ? 'upload' : 'automatic'
                ) !== 'upload')
                    hidden
                @endif
            >

                <label for="cover_image">
                    Pilih gambar sampul baru (opsional)
                </label>

                <div class="module-upload-box">
                    <p>Pilih gambar jika ingin mengganti sampul lama</p>

                    <input
                        type="file"
                        id="cover_image"
                        name="cover_image"
                        class="module-file-input"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >
                </div>

                <small class="module-help-text">
                    Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                    Kosongkan untuk mempertahankan sampul lama.
                </small>

            </div>

            <!-- STATUS SAMPUL OTOMATIS -->
            <div
                id="automatic-cover-status"
                class="module-help-text"
                role="status"
                aria-live="polite"
            >
                Sampul otomatis dibuat dari halaman pertama PDF saat disimpan.
            </div>

            @error('cover_mode')
                <div class="module-form-error">{{ $message }}</div>
            @enderror

            @error('cover_image')
                <div class="module-form-error">{{ $message }}</div>
            @enderror

        </div>

        <!-- TOMBOL FORM -->
        <div class="module-form-actions">

            <a
                href="{{ route('admin.modules.index') }}"
                class="module-button"
            >
                Batal
            </a>

            <button
                type="submit"
                id="save-module-button"
                class="module-button module-button-primary"
            >
                Simpan Perubahan
            </button>

        </div>

    </form>

</section>

@endsection

@push('scripts')

<!-- PDF.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('module-edit-form');
    const pdfInput = document.getElementById('pdf_file');
    const coverInput = document.getElementById('cover_image');
    const coverContainer = document.getElementById('cover-upload-container');
    const status = document.getElementById('automatic-cover-status');
    const saveButton = document.getElementById('save-module-button');
    const radios = document.querySelectorAll('input[name="cover_mode"]');

    if (
        !form ||
        !pdfInput ||
        !coverInput ||
        !coverContainer ||
        !status ||
        !saveButton
    ) {
        return;
    }

    const currentPdfUrl = form.dataset.currentPdfUrl || '';

    let isSubmitting = false;
    let generatedCover = false;

    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    /* MEMBACA MODE SAMPUL */
    function selectedMode() {
        const selected = document.querySelector(
            'input[name="cover_mode"]:checked'
        );

        return selected ? selected.value : 'automatic';
    }

    /* MENGATUR TAMPILAN PILIHAN SAMPUL */
    function updateCoverMode() {
        const automatic = selectedMode() === 'automatic';

        coverContainer.hidden = automatic;

        if (automatic) {
            status.textContent =
                'Sampul akan dibuat dari halaman pertama PDF saat formulir disimpan.';
        } else {
            status.textContent =
                'Unggah gambar sendiri, atau kosongkan untuk mempertahankan sampul lama.';

            if (generatedCover) {
                coverInput.value = '';
                generatedCover = false;
            }
        }
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', updateCoverMode);
    });

    /* MENGAMBIL DATA PDF */
    async function getPdfData() {
        const selectedPdf = pdfInput.files[0];

        if (selectedPdf) {
            return await selectedPdf.arrayBuffer();
        }

        if (!currentPdfUrl) {
            throw new Error(
                'Modul ini belum memiliki PDF. Pilih file PDF terlebih dahulu.'
            );
        }

        const response = await fetch(currentPdfUrl);

        if (!response.ok) {
            throw new Error(
                'PDF lama tidak dapat dibaca. Periksa file PDF atau unggah ulang PDF.'
            );
        }

        return await response.arrayBuffer();
    }

    /* MEMBUAT SAMPUL DARI HALAMAN PERTAMA PDF */
    async function generateCoverFromPdf() {
        if (!window.pdfjsLib) {
            throw new Error(
                'PDF.js gagal dimuat. Periksa koneksi internet, lalu coba lagi.'
            );
        }

        const pdfData = await getPdfData();

        const pdf = await pdfjsLib.getDocument({
            data: new Uint8Array(pdfData)
        }).promise;

        try {
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: 1.2 });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            if (!context) {
                throw new Error(
                    'Browser tidak dapat membuat gambar sampul.'
                );
            }

            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);

            await page.render({
                canvas: canvas,
                canvasContext: context,
                viewport: viewport
            }).promise;

            const blob = await new Promise(function (resolve, reject) {
                canvas.toBlob(function (result) {
                    if (result) {
                        resolve(result);
                    } else {
                        reject(
                            new Error('Gagal membuat gambar sampul.')
                        );
                    }
                }, 'image/jpeg', 0.75);
            });

            const file = new File(
                [blob],
                'sampul-modul.jpg',
                { type: 'image/jpeg' }
            );

            const transfer = new DataTransfer();
            transfer.items.add(file);

            coverInput.files = transfer.files;
            generatedCover = true;

        } finally {
            await pdf.destroy();
        }
    }

    /* PROSES SIMPAN FORM */
    form.addEventListener('submit', async function (event) {

        if (isSubmitting || selectedMode() !== 'automatic') {
            return;
        }

        event.preventDefault();

        try {
            saveButton.disabled = true;
            saveButton.textContent = 'Membuat Sampul...';

            status.textContent =
                'Sedang membaca PDF dan membuat gambar sampul...';

            await generateCoverFromPdf();

            status.textContent =
                'Sampul berhasil dibuat. Menyimpan perubahan...';

            isSubmitting = true;
            form.requestSubmit();

        } catch (error) {
            console.error(error);

            status.textContent =
                error.message || 'Gagal membuat sampul otomatis.';

            saveButton.disabled = false;
            saveButton.textContent = 'Simpan Perubahan';
        }
    });

    updateCoverMode();

});
</script>

@endpush
