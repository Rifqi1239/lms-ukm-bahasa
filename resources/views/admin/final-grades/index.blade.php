
@extends('layouts.admin')

@section('page_title', 'Penilaian Akhir')
@section('breadcrumb', 'Halaman / Penilaian Akhir')

@section('content')

<div class="final-grade-page">

    <div class="final-grade-header">
        <div>
            <h1>Penilaian Akhir</h1>
            <p>Kelola dan tinjau hasil penilaian akhir anggota.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (auth()->user()->role === 'admin')
        <form action="{{ route('admin.final-grades.index') }}"
              method="GET"
              class="final-grade-filter">

            <label for="language_group_id">Pilih Kelompok Bahasa</label>

            <select name="language_group_id"
                    id="language_group_id"
                    onchange="this.form.submit()">
                <option value="">-- Pilih Kelompok Bahasa --</option>

                @foreach ($groups as $group)
                    <option value="{{ $group->id }}"
                        {{ request('language_group_id') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif

    @if ($selectedGroup)

        <div class="final-grade-group-info">
            <h3>{{ $selectedGroup->name }}</h3>
            <p>Daftar hasil penilaian anggota kelompok {{ $selectedGroup->name }}.</p>
        </div>

        <form action="{{ route('admin.final-grades.calculate') }}"
              method="POST"
              onsubmit="return confirm('Hitung ulang nilai akhir seluruh anggota kelompok ini?')">

            @csrf

            @if (auth()->user()->role === 'admin')
                <input type="hidden"
                       name="language_group_id"
                       value="{{ $selectedGroup->id }}">
            @endif

            <button type="submit" class="final-grade-calculate-button">
                Hitung Ulang Nilai
            </button>
        </form>

        <div class="final-grade-table-wrapper">
            <table class="final-grade-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Anggota</th>
                        <th>Nilai Terbaru</th>
                        <th>Nilai Disetujui</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($members as $index => $member)
                        @php
                            $pending = $member->latestPendingRecord;
                            $approved = $member->latestApprovedRecord;
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <strong>{{ $member->name }}</strong>
                                <br>
                                <small>{{ $member->email }}</small>
                            </td>

                            <td>
                                @if ($pending)
                                    {{ number_format((float) $pending->final_score, 2) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if ($approved)
                                    {{ number_format((float) $approved->final_score, 2) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if ($pending)
                                    <span class="final-grade-status pending">
                                        Menunggu Persetujuan
                                    </span>
                                @elseif ($approved)
                                    <span class="final-grade-status approved">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="final-grade-status empty">
                                        Belum Dihitung
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($pending)
                                    <form action="{{ route('admin.final-grades.approve', $pending->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Setujui nilai akhir anggota ini?')">
                                        @csrf

                                        <button type="submit"
                                                class="final-grade-approve-button">
                                            Setujui
                                        </button>
                                    </form>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="final-grade-empty">
                                Belum ada anggota pada kelompok bahasa ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @else

        @if (auth()->user()->role === 'admin')
            <div class="final-grade-empty-state">
                Silakan pilih kelompok bahasa terlebih dahulu.
            </div>
        @endif

    @endif

</div>

@endsection
