
@extends('layouts.admin')

@section('page_title', 'Kelompok Bahasa')
@section('breadcrumb', 'Halaman / Kelompok Bahasa')

@section('content')

    <!-- HEADER HALAMAN -->
    <div class="member-page-header">
        <div>
            <h1>Kelompok Bahasa</h1>
            <p>Kelola kelompok bahasa LMS UKM Bahasa</p>
        </div>

        <a href="{{ route('admin.language-groups.create') }}"
            class="member-add-button">
            <span>＋</span>
            Tambah Kelompok
        </a>
    </div>

    <!-- PESAN NOTIFIKASI -->
    @if (session('success'))
        <div class="member-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="member-alert error">
            {{ session('error') }}
        </div>
    @endif

    <!-- STATISTIK -->
    <section class="member-stat-grid">

        <div class="member-stat-card">
            <div class="member-stat-icon blue">
                ◈
            </div>

            <div class="member-stat-info">
                <span>Total Kelompok</span>
                <h3>{{ number_format($totalKelompok) }}</h3>
            </div>
        </div>

        <div class="member-stat-card">
            <div class="member-stat-icon green">
                ✓
            </div>

            <div class="member-stat-info">
                <span>Kelompok Aktif</span>
                <h3>{{ number_format($kelompokAktif) }}</h3>
            </div>
        </div>

        <div class="member-stat-card">
            <div class="member-stat-icon gray">
                ⏸
            </div>

            <div class="member-stat-info">
                <span>Kelompok Nonaktif</span>
                <h3>{{ number_format($kelompokNonaktif) }}</h3>
            </div>
        </div>

        <div class="member-stat-card">
            <div class="member-stat-icon purple">
                ♙
            </div>

            <div class="member-stat-info">
                <span>Total Pengguna</span>
                <h3>{{ number_format($totalPengguna) }}</h3>
            </div>
        </div>

    </section>

    <!-- DAFTAR KELOMPOK BAHASA -->
    <section class="member-table-card">

        <!-- HEADER TABEL -->
        <div class="member-table-header">

            <div class="member-table-title">
                <div class="member-table-title-icon">
                    ◈
                </div>

                <div>
                    <h2>Daftar Kelompok Bahasa</h2>
                    <p>Kelola kelompok bahasa dan konten pembelajaran</p>
                </div>
            </div>

            <!-- FILTER -->
            <form
                action="{{ route('admin.language-groups.index') }}"
                method="GET"
                class="member-filter-form">

                <div class="member-search-box">
                    <span>⌕</span>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau kode bahasa..."
                    >
                </div>

                <select name="status" class="member-filter-select">
                    <option value="">Semua Status</option>

                    <option value="1"
                        {{ request('status') === '1' ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0"
                        {{ request('status') === '0' ? 'selected' : '' }}>
                        Tidak Aktif
                    </option>
                </select>

                <button type="submit" class="member-filter-submit">
                    Cari
                </button>

                @if (request()->hasAny(['search', 'status']))
                    <a
                        href="{{ route('admin.language-groups.index') }}"
                        class="member-filter-submit">
                        Reset
                    </a>
                @endif

            </form>

        </div>

        <!-- TABEL -->
        <div class="member-table-wrapper">

            <table class="member-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Kelompok</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Pengguna</th>
                        <th>Modul</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($languageGroups as $group)

                        <tr>
                            <td>
                                {{ $languageGroups->firstItem() + $loop->index }}
                            </td>

                            <td class="member-name">
                                {{ $group->name }}
                            </td>

                            <td>
                                <span class="member-role-badge">
                                    {{ $group->code }}
                                </span>
                            </td>

                            <td>
                                {{ $group->description ?: '—' }}
                            </td>

                            <td>
                                {{ number_format($group->users_count) }}
                            </td>

                            <td>
                                {{ number_format($group->modules_count) }}
                            </td>

                            <td>
                                @if ($group->is_active)
                                    <span class="member-status-badge active">
                                        Aktif
                                    </span>
                                @else
                                    <span class="member-status-badge inactive">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="member-action-buttons">

                                    <!-- EDIT -->
                                    <a
                                        href="{{ route('admin.language-groups.edit', $group->id) }}"
                                        class="member-action-button edit"
                                        title="Edit kelompok bahasa">
                                        ✎
                                    </a>

                                    <!-- TOGGLE STATUS -->
                                    <form
                                        action="{{ route('admin.language-groups.toggle-status', $group->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah kamu yakin ingin {{ $group->is_active ? 'menonaktifkan' : 'mengaktifkan' }} kelompok bahasa ini?')">

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="member-action-button {{ $group->is_active ? 'delete' : 'edit' }}"
                                            title="{{ $group->is_active ? 'Nonaktifkan kelompok' : 'Aktifkan kelompok' }}">

                                            {{ $group->is_active ? '⏸' : '✓' }}

                                        </button>

                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="member-empty">
                                Tidak ada kelompok bahasa yang ditemukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- FOOTER TABEL -->
        <div class="member-table-footer">

            <p>
                @if ($languageGroups->total() > 0)
                    Menampilkan {{ $languageGroups->firstItem() }}–{{ $languageGroups->lastItem() }}
                    dari {{ $languageGroups->total() }} kelompok bahasa
                @else
                    Menampilkan 0 data
                @endif
            </p>

            <div class="member-pagination">
                {{ $languageGroups->links() }}
            </div>

        </div>

    </section>

@endsection
