
@extends('layouts.admin')

@section('page_title', 'Kelola Anggota')
@section('breadcrumb', 'Halaman / Pengguna')

@section('content')

    <!-- HEADER HALAMAN -->
    <div class="member-page-header">
        <h1>Kelola Anggota</h1>

        <a href="{{ route('admin.members.create') }}" class="member-add-button">
            <span>＋</span>
            Tambah Anggota
        </a>
    </div>

    <!-- STATISTIK -->
    <section class="member-stat-grid">

        <div class="member-stat-card">
            <div class="member-stat-icon blue">
                ♙
            </div>

            <div class="member-stat-info">
                <span>Total Anggota</span>
                <h3>{{ number_format($totalAnggota) }}</h3>
            </div>
        </div>

        <div class="member-stat-card">
            <div class="member-stat-icon green">
                ✓
            </div>

            <div class="member-stat-info">
                <span>Anggota Aktif</span>
                <h3>{{ number_format($anggotaAktif) }}</h3>
            </div>
        </div>

        <div class="member-stat-card">
            <div class="member-stat-icon gray">
                ♟
            </div>

            <div class="member-stat-info">
                <span>Anggota per Kelompok</span>
                <h3>{{ number_format($anggotaPerKelompok) }}</h3>
            </div>
        </div>

        <div class="member-stat-card">
            <div class="member-stat-icon purple">
                ♛
            </div>

            <div class="member-stat-info">
                <span>Admin</span>
                <h3>{{ number_format($totalAdmin) }}</h3>
            </div>
        </div>

    </section>

    <!-- DAFTAR ANGGOTA -->
    <section class="member-table-card">

        <!-- HEADER TABEL -->
        <div class="member-table-header">

            <div class="member-table-title">
                <div class="member-table-title-icon">
                    ♟
                </div>

                <div>
                    <h2>Daftar Anggota</h2>
                    <p>Kelola data anggota LMS UKM Bahasa</p>
                </div>
            </div>

            <!-- FILTER -->
            <form
                action="{{ route('admin.members.index') }}"
                method="GET"
                class="member-filter-form"
            >

                <div class="member-search-box">
                    <span>⌕</span>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau email..."
                    >
                </div>

                <select name="role" class="member-filter-select">
                    <option value="">Semua Jenis</option>

                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>

                    <option value="pengajar" {{ request('role') === 'pengajar' ? 'selected' : '' }}>
                        Pengajar
                    </option>

                    <option value="anggota" {{ request('role') === 'anggota' ? 'selected' : '' }}>
                        Anggota
                    </option>
                </select>

                <select name="status" class="member-filter-select">
                    <option value="">Semua Status</option>

                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                        Tidak Aktif
                    </option>
                </select>

                <button type="submit" class="member-filter-submit">
                    Cari
                </button>

            </form>

        </div>

        <!-- PESAN -->
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

        <!-- TABEL -->
        <div class="member-table-wrapper">

            <table class="member-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Jenis</th>
                        <th>Kelompok Bahasa</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($users as $user)

                        <tr>
                            <td>
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td class="member-name">
                                {{ $user->name }}
                            </td>

                            <td>
                                {{ $user->email }}
                            </td>

                            <td>
                                <span class="member-role-badge {{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td>
                                {{ $user->languageGroup?->name ?? '—' }}
                            </td>

                            <td>
                                @if ($user->is_active)
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
                                {{ $user->created_at?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                <div class="member-action-buttons">

                                    <a
                                        href="{{ route('admin.members.edit', $user->id) }}"
                                        class="member-action-button edit"
                                        title="Edit pengguna"
                                    >
                                        ✎
                                    </a>

                                    <form
                                        action="{{ route('admin.members.destroy', $user->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Apakah kamu yakin ingin menghapus pengguna ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="member-action-button delete"
                                            title="Hapus pengguna"
                                        >
                                            ♜
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="member-empty">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- FOOTER TABEL -->
        <div class="member-table-footer">

            <p>
                @if ($users->total() > 0)
                    Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }}
                    dari {{ $users->total() }} data
                @else
                    Menampilkan 0 data
                @endif
            </p>

            <div class="member-pagination">
                {{ $users->links() }}
            </div>

        </div>

    </section>

@endsection
