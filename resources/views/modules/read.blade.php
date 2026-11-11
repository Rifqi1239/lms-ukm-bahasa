
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $module->title }} - UKM Bahasa</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            color: #202938;
        }

        body {
            overflow: hidden;
            background: #e5e7eb;
        }

        .reader-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #e5e7eb;
        }

        /* HEADER PEMBACA */
        .reader-header {
            min-height: 42px;
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: minmax(90px, 1fr) minmax(0, 2fr) minmax(0, 2fr);
            align-items: center;
            gap: 10px;
            padding: 7px 16px;
            background: #fff;
            border-bottom: 1px solid #d1d5db;
            font-size: 12px;
        }

        .back-link {
            color: #374151;
            text-decoration: none;
            white-space: nowrap;
        }

        .back-link:hover {
            color: #1d4ed8;
        }

        .reader-title {
            text-align: center;
            font-weight: 700;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .reader-tools {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .reader-tools button {
            border: 0;
            background: transparent;
            cursor: pointer;
            color: #374151;
            font-size: 12px;
            padding: 5px;
        }

        .reader-tools button:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        #fullscreen-button {
            padding: 6px 9px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            background: #fff;
        }

        #fullscreen-button:hover {
            background: #f3f4f6;
        }

        /* AREA MEMBACA PDF */
        .reader-main {
            position: relative;
            flex: 1;
            min-height: 0;
            display: flex;
            align-items: stretch;
            justify-content: center;
            padding: 12px 64px;
            overflow: hidden;
        }

        .pdf-viewport {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow: auto;
            overscroll-behavior: contain;
        }

        #pdf-canvas {
            display: block;
            flex: 0 0 auto;
            max-width: none;
            max-height: none;
            width: auto;
            height: auto;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #bfc6d1;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgb(0 0 0 / 8%);
        }

        /* TOMBOL HALAMAN */
        .page-button {
            position: absolute;
            z-index: 1;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #c5ccd6;
            border-radius: 50%;
            background: #fff;
            color: #344054;
            font-size: 22px;
            cursor: pointer;
        }

        .page-button:hover:not(:disabled) {
            background: #f3f4f6;
        }

        .page-button:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        #prev-page {
            left: 14px;
        }

        #next-page {
            right: 14px;
        }

        /* PESAN PEMUATAN ATAU KESALAHAN */
        .reader-message {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: calc(100% - 32px);
            padding: 12px 16px;
            border-radius: 6px;
            background: #fff;
            color: #374151;
            font-size: 13px;
            text-align: center;
            box-shadow: 0 2px 8px #0001;
        }

        .reader-message[hidden] {
            display: none;
        }

        /* FOOTER */
        .reader-footer {
            min-height: 42px;
            flex: 0 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 6px 22px;
            background: #fff;
            border-top: 1px solid #d1d5db;
        }

        .reader-note {
            color: #7b8493;
            font-size: 11px;
        }

        .exercise-button {
            display: inline-block;
            padding: 8px 20px;
            border: 0;
            border-radius: 5px;
            background: #374151;
            color: #fff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        .exercise-button:hover:not(:disabled) {
            background: #1f2937;
        }

        .exercise-button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        /* MODE LAYAR PENUH */
        .reader-container:fullscreen {
            width: 100%;
            height: 100%;
            background: #e5e7eb;
        }

        .reader-container:fullscreen .reader-header {
            min-height: 48px;
        }

        .reader-container:fullscreen .reader-footer {
            min-height: 46px;
        }

        /* TAMPILAN LAYAR KECIL */
        @media (max-width: 700px) {
            .reader-header {
                grid-template-columns: auto minmax(0, 1fr);
                gap: 6px;
                padding: 7px 8px;
            }

            .reader-title {
                grid-column: 2;
                grid-row: 1;
                text-align: right;
            }

            .reader-tools {
                grid-column: 1 / -1;
                justify-content: center;
                flex-wrap: wrap;
                gap: 8px;
            }

            #fullscreen-button {
                padding: 5px 8px;
            }

            .reader-main {
                padding: 10px 40px;
            }

            #prev-page {
                left: 4px;
            }

            #next-page {
                right: 4px;
            }

            .reader-footer {
                padding: 7px 10px;
            }

            .reader-note {
                max-width: 55%;
                line-height: 1.4;
                font-size: 10px;
            }

            .exercise-button {
                padding: 8px 10px;
                font-size: 10px;
            }
        }

        @media (max-width: 380px) {
            .reader-tools {
                gap: 4px;
            }

            .reader-tools button,
            #page-label,
            #zoom-label {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>

    <div
        class="reader-container"
        data-pdf-url="{{ route('modules.pdf-view', $module) }}"
        data-progress-url="{{ route('modules.progress.store', $module) }}"
        data-current-page="{{ $readingProgress?->current_page ?? 1 }}">

        {{-- HEADER --}}
        <header class="reader-header">

            <a class="back-link" href="{{ route('modules.index') }}">
                ← Kembali
            </a>

            <div class="reader-title">
                {{ $module->title }}
            </div>

            <div class="reader-tools">

                <button
                    type="button"
                    id="fullscreen-button"
                    aria-label="Aktifkan layar penuh">
                    ⛶ Full Screen
                </button>

                <button type="button" id="zoom-out" aria-label="Perkecil">
                    −
                </button>

                <span id="zoom-label">100%</span>

                <button type="button" id="zoom-in" aria-label="Perbesar">
                    +
                </button>

                <span id="page-label">Halaman 0 / 0</span>

            </div>

        </header>

        {{-- AREA PDF --}}
        <main class="reader-main">

            <button
                type="button"
                class="page-button"
                id="prev-page"
                aria-label="Halaman sebelumnya"
                disabled>
                ‹
            </button>

            <div class="pdf-viewport" id="pdf-viewport">
                <canvas id="pdf-canvas"></canvas>
            </div>

            <button
                type="button"
                class="page-button"
                id="next-page"
                aria-label="Halaman berikutnya"
                disabled>
                ›
            </button>

            <div class="reader-message" id="reader-message" role="status">
                Memuat PDF...
            </div>

        </main>

        {{-- FOOTER --}}
        <footer class="reader-footer">

            <span class="reader-note">
                Selesai membaca? Latihan membantu mengulang materi.
            </span>

            @if ($module->questions()->exists())

                <a
                    href="{{ route('quiz.start', ['module' => $module->id]) }}"
                    class="exercise-button"
                    id="exercise-button">
                    Kerjakan Latihan (opsional)
                </a>

            @else

                <button
                    type="button"
                    class="exercise-button"
                    id="exercise-button"
                    disabled
                    aria-disabled="true"
                    title="Modul ini belum memiliki soal latihan">
                    Tidak Ada Latihan
                </button>

            @endif

        </footer>

    </div>

    {{-- PDF.JS DAN PENYIMPANAN PROGRES --}}
    <script type="module">

        import * as pdfjsLib from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs';

        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.mjs';

        // ELEMENT HTML
        const readerContainer = document.querySelector('.reader-container');

        const pdfUrl = readerContainer.dataset.pdfUrl;
        const progressUrl = readerContainer.dataset.progressUrl;

        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');

        const viewportContainer = document.getElementById('pdf-viewport');
        const message = document.getElementById('reader-message');

        const pageLabel = document.getElementById('page-label');
        const zoomLabel = document.getElementById('zoom-label');

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');

        const zoomInButton = document.getElementById('zoom-in');
        const zoomOutButton = document.getElementById('zoom-out');

        const fullscreenButton = document.getElementById('fullscreen-button');

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        ).content;

        // VARIABEL PDF
        let pdfDocument = null;

        let currentPage = Number(
            readerContainer.dataset.currentPage
        ) || 1;

        let zoom = 1;
        let renderTask = null;
        let renderVersion = 0;

        // ANTRIAN PENYIMPANAN PROGRES
        // Menjaga urutan permintaan agar data halaman terbaru tidak tertimpa.
        let saveQueue = Promise.resolve();

        /**
         * Menyimpan progres membaca ke database.
         */
        function saveReadingProgress() {

            if (!pdfDocument) {
                return Promise.resolve();
            }

            const progressData = {
                current_page: currentPage,
                total_pages: pdfDocument.numPages
            };

            saveQueue = saveQueue
                .catch(() => {
                    // Memungkinkan antrian berikutnya tetap berjalan.
                })
                .then(async () => {

                    const response = await fetch(progressUrl, {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },

                        body: JSON.stringify(progressData)
                    });

                    if (!response.ok) {
                        throw new Error(
                            'Gagal menyimpan progres membaca.'
                        );
                    }

                    const result = await response.json();

                    console.log(
                        'Progres membaca berhasil disimpan:',
                        result
                    );

                    return result;
                });

            return saveQueue.catch(error => {
                console.error(
                    'Kesalahan penyimpanan progres:',
                    error
                );
            });
        }

        /**
         * Menampilkan halaman PDF dengan ukuran yang sesuai.
         */
        async function renderPage() {

            if (!pdfDocument) return;

            const version = ++renderVersion;

            if (renderTask) {
                renderTask.cancel();
                renderTask = null;
            }

            try {

                const page = await pdfDocument.getPage(currentPage);

                if (version !== renderVersion) return;

                const baseViewport = page.getViewport({
                    scale: 1
                });

                const availableWidth = Math.max(
                    100,
                    viewportContainer.clientWidth - 16
                );

                const availableHeight = Math.max(
                    100,
                    viewportContainer.clientHeight - 16
                );

                const widthScale =
                    availableWidth / baseViewport.width;

                const heightScale =
                    availableHeight / baseViewport.height;

                // Buku portrait mengikuti lebar area baca.
                // Slide landscape menyesuaikan lebar dan tinggi.
                const isPortrait =
                    baseViewport.height > baseViewport.width;

                const fitScale = isPortrait
                    ? widthScale
                    : Math.min(widthScale, heightScale);

                const scale = fitScale * zoom;

                const viewport = page.getViewport({
                    scale
                });

                const outputScale = window.devicePixelRatio || 1;

                canvas.width = Math.floor(
                    viewport.width * outputScale
                );

                canvas.height = Math.floor(
                    viewport.height * outputScale
                );

                canvas.style.width = `${viewport.width}px`;
                canvas.style.height = `${viewport.height}px`;

                const transform = outputScale !== 1
                    ? [
                        outputScale, 0,
                        0, outputScale,
                        0, 0
                    ]
                    : null;

                renderTask = page.render({
                    canvasContext: ctx,
                    viewport,
                    transform
                });

                await renderTask.promise;

                if (version !== renderVersion) return;

                message.hidden = true;

                pageLabel.textContent =
                    `Halaman ${currentPage} / ${pdfDocument.numPages}`;

                zoomLabel.textContent =
                    `${Math.round(zoom * 100)}%`;

                prevButton.disabled = currentPage <= 1;

                nextButton.disabled =
                    currentPage >= pdfDocument.numPages;

                zoomOutButton.disabled = zoom <= 0.5;
                zoomInButton.disabled = zoom >= 2;

                // Simpan progres setelah halaman berhasil ditampilkan.
                await saveReadingProgress();

            } catch (error) {

                if (error?.name !== 'RenderingCancelledException') {

                    console.error(error);

                    message.textContent =
                        'Gagal menampilkan halaman PDF.';

                    message.hidden = false;
                }
            }
        }

        /**
         * Memuat dokumen PDF.
         */
        async function loadPdf() {

            try {

                pdfDocument = await pdfjsLib.getDocument({
                    url: pdfUrl
                }).promise;

                if (pdfDocument.numPages < 1) {
                    throw new Error(
                        'Dokumen PDF tidak memiliki halaman.'
                    );
                }

                // Memastikan halaman tersimpan tidak melebihi
                // jumlah halaman PDF saat ini.
                currentPage = Math.min(
                    Math.max(currentPage, 1),
                    pdfDocument.numPages
                );

                await renderPage();

            } catch (error) {

                console.error(error);

                message.textContent =
                    'PDF gagal dimuat. Periksa koneksi internet, akses file, dan file PDF modul.';

                message.hidden = false;
            }
        }

        // BERPINDAH KE HALAMAN SEBELUMNYA
        prevButton.addEventListener('click', () => {

            if (currentPage > 1) {

                currentPage--;

                renderPage();
            }
        });

        // BERPINDAH KE HALAMAN BERIKUTNYA
        nextButton.addEventListener('click', () => {

            if (
                pdfDocument &&
                currentPage < pdfDocument.numPages
            ) {

                currentPage++;

                renderPage();
            }
        });

        // MEMPERBESAR TAMPILAN
        zoomInButton.addEventListener('click', () => {

            zoom = Math.min(
                Math.round((zoom + 0.1) * 10) / 10,
                2
            );

            renderPage();
        });

        // MEMPERKECIL TAMPILAN
        zoomOutButton.addEventListener('click', () => {

            zoom = Math.max(
                Math.round((zoom - 0.1) * 10) / 10,
                0.5
            );

            renderPage();
        });

        /**
         * Memperbarui teks tombol sesuai status layar penuh.
         */
        function updateFullscreenButton() {

            const isFullscreen =
                document.fullscreenElement === readerContainer;

            fullscreenButton.textContent = isFullscreen
                ? '✕ Keluar Full Screen'
                : '⛶ Full Screen';

            fullscreenButton.setAttribute(
                'aria-label',
                isFullscreen
                    ? 'Keluar dari layar penuh'
                    : 'Aktifkan layar penuh'
            );
        }

        // MENGAKTIFKAN ATAU KELUAR DARI LAYAR PENUH
        fullscreenButton.addEventListener('click', async () => {

            try {

                if (document.fullscreenElement === readerContainer) {

                    await document.exitFullscreen();

                } else if (!document.fullscreenElement) {

                    await readerContainer.requestFullscreen();
                }

            } catch (error) {

                console.error(
                    'Gagal mengubah mode layar penuh:',
                    error
                );

                message.textContent =
                    'Mode layar penuh tidak dapat diaktifkan oleh browser ini.';

                message.hidden = false;
            }
        });

        // MENYESUAIKAN PDF KETIKA STATUS LAYAR PENUH BERUBAH
        document.addEventListener('fullscreenchange', () => {

            updateFullscreenButton();

            requestAnimationFrame(() => {
                renderPage();
            });
        });

        // MENYESUAIKAN PDF KETIKA UKURAN JENDELA BERUBAH
        let resizeTimer;

        window.addEventListener('resize', () => {

            clearTimeout(resizeTimer);

            resizeTimer = setTimeout(() => {
                renderPage();
            }, 150);
        });

        // INISIALISASI READER
        updateFullscreenButton();

        loadPdf();

    </script>

</body>

</html>
