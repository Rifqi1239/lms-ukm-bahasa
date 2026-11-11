
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Soal | LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="lms-page admin-page">

    <div class="admin-layout">

        <!-- SIDEBAR -->
        <aside class="admin-sidebar">

            <a href="{{ route('admin.dashboard') }}" class="admin-brand">

                <img
                    src="{{ asset('assets/images/logo-ukm-bahasa.png') }}"
                    alt="Logo UKM Bahasa"
                    class="sidebar-logo"
                >

                <div class="admin-brand-text">
                    <span>LMS UKM Bahasa</span>
                    <small>Administrator</small>
                </div>

            </a>

            <div class="admin-sidebar-divider"></div>

            <p class="admin-sidebar-label">MENU UTAMA</p>

            <nav class="admin-sidebar-nav">

                <a href="{{ route('admin.dashboard') }}"
                    class="admin-sidebar-link">
                    <span class="admin-menu-icon">▦</span>
                    <span>Dashboard</span>
                </a>

                <a href="#" class="admin-sidebar-link">
                    <span class="admin-menu-icon">♙</span>
                    <span>Pengguna</span>
                </a>

                <a href="#" class="admin-sidebar-link">
                    <span class="admin-menu-icon">▤</span>
                    <span>Kelompok Bahasa</span>
                </a>

                <a href="{{ route('admin.modules.index') }}"
                    class="admin-sidebar-link">
                    <span class="admin-menu-icon">▣</span>
                    <span>Kelola Modul</span>
                </a>

                <a href="{{ route('admin.questions.index') }}"
                    class="admin-sidebar-link active">
                    <span class="admin-menu-icon">☷</span>
                    <span>Soal Latihan</span>
                </a>

                <a href="#" class="admin-sidebar-link">
                    <span class="admin-menu-icon">▥</span>
                    <span>Hasil Belajar</span>
                </a>

                <a href="#" class="admin-sidebar-link">
                    <span class="admin-menu-icon">✓</span>
                    <span>Penilaian Akhir</span>
                </a>

            </nav>

            <!-- PROFIL ADMIN DAN LOGOUT -->
            <div class="admin-sidebar-bottom">

                <div class="admin-profile">

                    <div class="admin-profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="admin-profile-info">
                        <span>{{ auth()->user()->name }}</span>
                        <small>Administrator</small>
                    </div>

                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button type="submit" class="admin-logout-button">
                        Keluar dari Sistem
                    </button>
                </form>

            </div>

        </aside>

        <!-- AREA KONTEN -->
        <div class="admin-main">

            <!-- TOPBAR -->
            <header class="admin-topbar">

                <div>
                    <span class="admin-breadcrumb">
                        Halaman / Soal Latihan / Tambah Soal
                    </span>

                    <h2>Tambah Soal Latihan</h2>
                </div>

                <div class="admin-topbar-user">

                    <div class="admin-profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="admin-profile-info">
                        <span>{{ auth()->user()->name }}</span>
                        <small>Admin</small>
                    </div>

                </div>

            </header>

            <!-- KONTEN UTAMA -->
            <main class="admin-content">

                <!-- SAMBUTAN -->
                <section class="admin-welcome">

                    <p class="eyebrow">MANAJEMEN PEMBELAJARAN</p>

                    <h1>Tambah Soal Baru</h1>

                    <p>
                        Buat soal latihan untuk modul bahasa.
                        Lengkapi pertanyaan, lima pilihan jawaban,
                        dan tentukan jawaban yang benar.
                    </p>

                </section>

                <!-- FORM SOAL -->
                <section class="admin-dashboard-section">

                    <div class="admin-section-heading">
                        <div>
                            <h2>Informasi Soal</h2>
                            <p>
                                Isikan informasi soal dengan lengkap.
                            </p>
                        </div>
                    </div>

                    <!-- PESAN VALIDASI -->
                    @if ($errors->any())

                        <div class="admin-stat-card question-validation-card">

                            <strong>
                                Periksa kembali data berikut:
                            </strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>

                        </div>

                    @endif

                    @if ($modules->isEmpty())

                        <!-- JIKA BELUM ADA MODUL -->
                        <div class="admin-stat-card">

                            <h3>Modul belum tersedia</h3>

                            <p>
                                Tambahkan modul pembelajaran terlebih dahulu
                                sebelum membuat soal.
                            </p>

                            <a href="{{ route('admin.modules.create') }}">
                                Kelola Modul
                            </a>

                        </div>

                    @else

                        <!-- FORM TAMBAH SOAL -->
                        <div class="admin-stat-card">

                            <form
                                action="{{ route('admin.questions.store') }}"
                                method="POST"
                            >

                                @csrf

                                <!-- PILIH MODUL -->
                                <div class="question-form-group">

                                    <label for="module_id">
                                        Modul Bahasa
                                    </label>

                                    <select
                                        id="module_id"
                                        name="module_id"
                                        class="question-form-control"
                                        required
                                    >

                                        <option value="">
                                            -- Pilih Modul --
                                        </option>

                                        @foreach ($modules as $module)

                                            <option
                                                value="{{ $module->id }}"
                                                @selected(old('module_id') == $module->id)
                                            >
                                                {{ $module->title }}
                                            </option>

                                        @endforeach

                                    </select>

                                </div>

                                <!-- PERTANYAAN -->
                                <div class="question-form-group">

                                    <label for="question_text">
                                        Pertanyaan
                                    </label>

                                    <textarea
                                        id="question_text"
                                        name="question_text"
                                        class="question-form-control"
                                        rows="4"
                                        required
                                        placeholder="Tuliskan pertanyaan di sini..."
                                    >{{ old('question_text') }}</textarea>

                                </div>

                                <!-- PILIHAN JAWABAN -->
                                <div class="question-options-section">

                                    <h3>Pilihan Jawaban</h3>

                                    <p>
                                        Isi pilihan A sampai E, kemudian
                                        pilih tepat satu jawaban yang benar.
                                    </p>

                                    @foreach (['A', 'B', 'C', 'D', 'E'] as $label)

                                        <div class="admin-activity-item question-option-item">

                                            <label for="option_{{ $label }}">
                                                Pilihan {{ $label }}
                                            </label>

                                            <input
                                                type="text"
                                                id="option_{{ $label }}"
                                                name="options[{{ $label }}]"
                                                class="question-form-control"
                                                value="{{ old('options.'.$label) }}"
                                                required
                                                maxlength="1000"
                                                placeholder="Masukkan pilihan {{ $label }}"
                                            >

                                            <label class="question-correct-answer">

                                                <input
                                                    type="radio"
                                                    name="correct_option"
                                                    value="{{ $label }}"
                                                    @checked(old('correct_option') === $label)
                                                    required
                                                >

                                                Jadikan pilihan {{ $label }}
                                                sebagai jawaban benar

                                            </label>

                                        </div>

                                    @endforeach

                                </div>

                                <!-- PEMBAHASAN -->
                                <div class="question-form-group">

                                    <label for="explanation">
                                        Pembahasan
                                        <span>(Opsional)</span>
                                    </label>

                                    <textarea
                                        id="explanation"
                                        name="explanation"
                                        class="question-form-control"
                                        rows="4"
                                        placeholder="Jelaskan mengapa jawaban tersebut benar..."
                                    >{{ old('explanation') }}</textarea>

                                </div>

                                <!-- TOMBOL AKSI -->
                                <div class="question-form-actions">

                                    <button type="submit">
                                        Simpan Soal
                                    </button>

                                    <a href="{{ route('admin.questions.index') }}">
                                        Batal
                                    </a>

                                </div>

                            </form>

                        </div>

                    @endif

                </section>

                <!-- FOOTER -->
                <footer class="admin-footer">
                    LMS UKM Bahasa &copy; {{ date('Y') }}
                </footer>

            </main>

        </div>

    </div>

</body>

</html>
