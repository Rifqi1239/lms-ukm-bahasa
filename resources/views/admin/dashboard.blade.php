
@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

    @php
        $isAdmin = auth()->user()->role === 'admin';
    @endphp

    <!-- SAMBUTAN -->
    <section class="admin-welcome">

        <p class="eyebrow">
            {{ $isAdmin ? 'PANEL ADMINISTRATOR' : 'PANEL PENGAJAR' }}
        </p>

        <h1>
            Selamat Datang, {{ auth()->user()->name }}
        </h1>

        <p>
            @if ($isAdmin)
                Berikut ringkasan pengelolaan LMS UKM Bahasa.
                Pantau pengguna, kelompok bahasa, modul, dan
                aktivitas pembelajaran melalui halaman ini.
            @else
                Berikut ringkasan aktivitas pembelajaran kelompok bahasa Anda.
                Pantau jumlah anggota, modul, soal latihan, dan
                perkembangan pembelajaran melalui halaman ini.
            @endif
        </p>

    </section>

    <!-- RINGKASAN SISTEM -->
    <section class="admin-dashboard-section">

        <div class="admin-section-heading">
            <div>
                <h2>Ringkasan Sistem</h2>

                <p>
                    {{ $isAdmin
                        ? 'Gambaran umum seluruh data LMS UKM Bahasa.'
                        : 'Gambaran umum data kelompok bahasa Anda.' }}
                </p>
            </div>
        </div>

        <div class="admin-stats">

            <!-- TOTAL PENGGUNA / ANGGOTA -->
            <div class="admin-stat-card">

                <div class="admin-stat-top">
                    <h3>{{ $isAdmin ? 'Total Pengguna' : 'Total Anggota' }}</h3>
                    <span class="admin-stat-icon blue">♙</span>
                </div>

                <p class="stat-number">
                    {{ $totalUsers }}
                </p>

                <span class="admin-stat-caption">
                    {{ $isAdmin ? 'Seluruh akun terdaftar' : 'Anggota kelompok Anda' }}
                </span>

            </div>

            <!-- KELOMPOK BAHASA -->
            <div class="admin-stat-card">

                <div class="admin-stat-top">
                    <h3>Kelompok Bahasa</h3>
                    <span class="admin-stat-icon red">▤</span>
                </div>

                <p class="stat-number">
                    {{ $totalLanguageGroups }}
                </p>

                <span class="admin-stat-caption">
                    {{ $isAdmin ? 'Seluruh kelompok pembelajaran' : 'Kelompok yang Anda ampu' }}
                </span>

            </div>

            <!-- TOTAL MODUL -->
            <div class="admin-stat-card">

                <div class="admin-stat-top">
                    <h3>Total Modul</h3>
                    <span class="admin-stat-icon blue">▣</span>
                </div>

                <p class="stat-number">
                    {{ $totalModules }}
                </p>

                <span class="admin-stat-caption">
                    {{ $isAdmin ? 'Seluruh materi pembelajaran' : 'Materi kelompok Anda' }}
                </span>

            </div>

            <!-- SOAL LATIHAN -->
            <div class="admin-stat-card">

                <div class="admin-stat-top">
                    <h3>Soal Latihan</h3>
                    <span class="admin-stat-icon red">☷</span>
                </div>

                <p class="stat-number stat-accent">
                    {{ $totalQuestions }}
                </p>

                <span class="admin-stat-caption">
                    {{ $isAdmin ? 'Seluruh soal dalam modul' : 'Soal dalam modul kelompok Anda' }}
                </span>

            </div>

        </div>

    </section>

    <!-- AKTIVITAS PENGELOLAAN -->
    <section class="admin-dashboard-section">

        <div class="admin-section-heading">
            <div>
                <h2>
                    {{ $isAdmin ? 'Aktivitas Pengelolaan' : 'Pengelolaan Pembelajaran' }}
                </h2>

                <p>
                    {{ $isAdmin
                        ? 'Pilih bagian yang ingin kamu kelola.'
                        : 'Pilih fitur pembelajaran yang ingin kamu kelola.' }}
                </p>
            </div>
        </div>

        <div class="admin-activity-list">

            @if ($isAdmin)

                <!-- MANAJEMEN PENGGUNA -->
                <a href="{{ route('admin.members.index') }}"
                    class="admin-activity-item">

                    <div class="admin-activity-icon">♙</div>

                    <div>
                        <h3>Manajemen Pengguna</h3>
                        <p>
                            Kelola akun admin, pengajar, dan anggota.
                        </p>
                    </div>

                </a>

                <!-- KELOMPOK BAHASA -->
                <a href="{{ route('admin.language-groups.index') }}"
                    class="admin-activity-item">

                    <div class="admin-activity-icon">▤</div>

                    <div>
                        <h3>Kelompok Bahasa</h3>
                        <p>
                            Atur kelompok bahasa dan penempatan pengguna.
                        </p>
                    </div>

                </a>

            @endif

            <!-- MODUL PEMBELAJARAN -->
            <a href="{{ route('admin.modules.index') }}"
                class="admin-activity-item">

                <div class="admin-activity-icon">▣</div>

                <div>
                    <h3>Modul Pembelajaran</h3>
                    <p>
                        Kelola materi pembelajaran dan dokumen modul.
                    </p>
                </div>

            </a>

            <!-- SOAL LATIHAN -->
            <a href="{{ route('admin.questions.index') }}"
                class="admin-activity-item">

                <div class="admin-activity-icon">☷</div>

                <div>
                    <h3>Soal Latihan</h3>
                    <p>
                        Kelola pertanyaan dan pilihan jawaban latihan.
                    </p>
                </div>

            </a>

            <!-- HASIL BELAJAR -->
            <a href="{{ route('admin.learning-results.index') }}"
                class="admin-activity-item">

                <div class="admin-activity-icon">▥</div>

                <div>
                    <h3>Hasil Belajar</h3>
                    <p>
                        Pantau progres membaca dan hasil latihan anggota.
                    </p>
                </div>

            </a>

            <!-- PENILAIAN AKHIR -->
            <a href="{{ route('admin.final-grades.index') }}"
                class="admin-activity-item">

                <div class="admin-activity-icon">✓</div>

                <div>
                    <h3>Penilaian Akhir</h3>
                    <p>
                        Kelola perhitungan dan persetujuan nilai akhir anggota.
                    </p>
                </div>

            </a>

        </div>

    </section>

@endsection
