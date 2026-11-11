
@extends('layouts.admin')

@section('page_title', 'Hasil Belajar')
@section('breadcrumb', 'Halaman / Hasil Belajar')

@section('content')

<div class="learning-result-page">

    <div class="learning-result-header">
        <div>
            <h1>Hasil Belajar</h1>
            <p>Pantau perkembangan belajar dan aktivitas latihan anggota.</p>
        </div>
    </div>

    {{-- PILIH KELOMPOK BAHASA --}}
    <div class="learning-result-filter">
        <form action="{{ route('admin.learning-results.index') }}" method="GET">
            <div class="learning-result-filter-group">
                <label for="language_group_id">Kelompok Bahasa</label>

                @if(auth()->user()->role === 'admin')
                    <select name="language_group_id" id="language_group_id"
                        onchange="this.form.submit()">
                        <option value="">Pilih kelompok bahasa</option>

                        @foreach($groups as $group)
                            <option value="{{ $group->id }}"
                                {{ $selectedGroup?->id == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text"
                        value="{{ $selectedGroup?->name ?? '-' }}"
                        readonly>
                @endif
            </div>
        </form>
    </div>

    @if($selectedGroup)

        {{-- RINGKASAN --}}
        <div class="learning-result-statistics">

            <div class="learning-result-stat-card">
                <span>Total Anggota</span>
                <h2>{{ $totalMembers }}</h2>
                <p>Anggota dalam kelompok bahasa</p>
            </div>

            <div class="learning-result-stat-card">
                <span>Anggota dengan Progres</span>
                <h2>{{ $membersWithProgress }}</h2>
                <p>Sudah memiliki progres membaca</p>
            </div>

            <div class="learning-result-stat-card">
                <span>Rata-rata Progres Membaca</span>
                <h2>{{ number_format($averageProgress, 1) }}%</h2>
                <p>Rata-rata progres seluruh aktivitas membaca</p>
            </div>

            <div class="learning-result-stat-card">
                <span>Latihan Diselesaikan</span>
                <h2>{{ $totalCompletedAttempts }}</h2>
                <p>Total latihan yang telah dikumpulkan</p>
            </div>

        </div>

        {{-- TABEL ANGGOTA --}}
        <div class="learning-result-table-card">

            <div class="learning-result-table-header">
                <div>
                    <h2>Perkembangan Anggota</h2>
                    <p>Ringkasan aktivitas belajar setiap anggota.</p>
                </div>
            </div>

            <div class="learning-result-table-wrapper">
                <table class="learning-result-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Anggota</th>
                            <th>Modul Dibaca</th>
                            <th>Rata-rata Progres</th>
                            <th>Latihan Selesai</th>
                            <th>Nilai Latihan Terbaik</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($members as $index => $member)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <div class="learning-result-member">
                                        <strong>{{ $member->name }}</strong>
                                        <small>{{ $member->email }}</small>
                                    </div>
                                </td>

                                <td>
                                    {{ $member->total_reading_modules }}
                                </td>

                                <td>
                                    <div class="learning-result-progress">
                                        <div class="learning-result-progress-track">
                                            <div class="learning-result-progress-fill"
                                                style="width: {{ min(100, max(0, $member->average_progress ?? 0)) }}%">
                                            </div>
                                        </div>

                                        <span>
                                            {{ number_format($member->average_progress ?? 0, 1) }}%
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    {{ $member->completed_attempts }}
                                </td>

                                <td>
                                    @if($member->best_quiz_score !== null)
                                        {{ number_format($member->best_quiz_score, 1) }}%
                                    @else
                                        <span class="learning-result-empty">Belum ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="learning-result-empty-row">
                                    Belum ada anggota pada kelompok bahasa ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    @else

        <div class="learning-result-placeholder">
            <h2>Pilih Kelompok Bahasa</h2>
            <p>
                Silakan pilih kelompok bahasa terlebih dahulu untuk melihat
                perkembangan belajar anggotanya.
            </p>
        </div>

    @endif

</div>

@endsection
