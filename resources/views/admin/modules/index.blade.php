
@extends('layouts.admin')

@section('title', 'Kelola Modul | LMS UKM Bahasa')

@section('breadcrumb', 'Halaman / Kelola Modul')

@section('page-title', 'Kelola Modul')

@section('content')

    <section class="modules-content">

        {{-- NOTIFIKASI SUKSES --}}
        @if (session('success'))
            <div class="modules-alert modules-alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- NOTIFIKASI ERROR --}}
        @if (session('error'))
            <div class="modules-alert modules-alert-error">
                {{ session('error') }}
            </div>
        @endif

        {{-- ERROR VALIDASI --}}
        @if ($errors->any())
            <div class="modules-alert modules-alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- JUDUL DAN TOMBOL TAMBAH --}}
        <div class="modules-heading">

            <div>
                <h1>Daftar Modul</h1>

                <p>
                    Kelola materi pembelajaran untuk setiap
                    kelompok bahasa.
                </p>
            </div>

            <a
                href="{{ route('admin.modules.create') }}"
                class="modules-add-button"
            >
                + Tambah Modul
            </a>

        </div>

        {{-- PENCARIAN MODUL --}}
        <form
            action="{{ route('admin.modules.index') }}"
            method="GET"
            class="modules-toolbar"
        >

            <input
                type="search"
                name="search"
                class="modules-search"
                placeholder="Cari judul modul..."
                value="{{ request('search') }}"
            >

            <button type="submit" class="modules-add-button">
                Cari
            </button>

            @if (request()->filled('search'))
                <a
                    href="{{ route('admin.modules.index') }}"
                    class="modules-reset-link"
                >
                    Reset
                </a>
            @endif

        </form>

        {{-- TABEL DAFTAR MODUL --}}
        <div class="modules-table-wrapper">

            <table class="modules-table">

                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Nama Modul</th>
                        <th>Kelompok Bahasa</th>
                        <th>Jumlah Soal</th>
                        <th>Diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($modules as $module)

                        <tr>

                            {{-- COVER MODUL --}}
                            <td>

                                @if ($module->cover_image)

                                    <img
                                        src="{{ asset('storage/' . $module->cover_image) }}"
                                        alt="Cover {{ $module->title }}"
                                        class="module-cover"
                                    >

                                @else

                                    <div class="module-cover">
                                        —
                                    </div>

                                @endif

                            </td>

                            {{-- NAMA DAN DESKRIPSI MODUL --}}
                            <td>

                                <div class="module-title">
                                    {{ $module->title }}
                                </div>

                                @if ($module->description)
                                    <div class="module-description">
                                        {{ \Illuminate\Support\Str::limit($module->description, 65) }}
                                    </div>
                                @endif

                            </td>

                            {{-- KELOMPOK BAHASA --}}
                            <td>

                                <span class="language-badge">
                                    {{ $module->languageGroup->name ?? 'Belum diatur' }}
                                </span>

                            </td>

                            {{-- JUMLAH SOAL --}}
                            <td>

                                @if ($module->questions_count > 0)

                                    {{ $module->questions_count }} soal

                                @else

                                    <span class="module-no-questions">
                                        Belum memiliki soal
                                    </span>

                                @endif

                            </td>

                            {{-- TANGGAL DIPERBARUI --}}
                            <td>
                                {{ $module->updated_at?->format('d M Y') ?? '—' }}
                            </td>

                            {{-- AKSI EDIT DAN HAPUS --}}
                            <td>

                                <div class="module-actions">

                                    <a href="{{ route('admin.modules.edit', $module) }}">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.modules.destroy', $module) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus modul ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit">
                                            Hapus
                                        </button>
                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="modules-empty">

                                @if (request()->filled('search'))

                                    Modul dengan judul tersebut tidak ditemukan.

                                @else

                                    Belum ada modul yang tersedia.

                                @endif

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="modules-pagination">
            {{ $modules->links() }}
        </div>

    </section>

@endsection
