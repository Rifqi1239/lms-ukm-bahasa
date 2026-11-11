
@extends('layouts.admin')

@section('title', 'Tambah Anggota | LMS UKM Bahasa')
@section('breadcrumb', 'Halaman / Pengguna / Tambah Anggota')
@section('page-title', 'Tambah Anggota')

@section('content')

<div class="member-page-header">
    <div>
        <h1>Tambah Anggota</h1>
        <p>Tambahkan pengguna baru ke dalam LMS UKM Bahasa.</p>
    </div>

    <a href="{{ route('admin.members.index') }}" class="member-add-button">
        Kembali
    </a>
</div>

<section class="member-table-card">

    <div class="member-table-header">
        <div class="member-table-title">
            <div class="member-table-title-icon">
                ♙
            </div>

            <div>
                <h2>Formulir Anggota</h2>
                <p>Lengkapi informasi pengguna berikut.</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="member-alert error">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.members.store') }}"
        method="POST"
        class="member-form"
    >
        @csrf

        <div class="member-form-grid">

            <div class="member-form-group">
                <label for="name">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap"
                    required
                >
                @error('name')
                    <small class="member-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="member-form-group">
                <label for="email">Alamat Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="contoh@email.com"
                    required
                >
                @error('email')
                    <small class="member-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="member-form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    required
                >
                @error('password')
                    <small class="member-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="member-form-group">
                <label for="role">Jenis Pengguna</label>
                <select id="role" name="role" required>
                    <option value="">Pilih jenis pengguna</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="pengajar" {{ old('role') === 'pengajar' ? 'selected' : '' }}>Pengajar</option>
                    <option value="anggota" {{ old('role') === 'anggota' ? 'selected' : '' }}>Anggota</option>
                </select>
                @error('role')
                    <small class="member-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="member-form-group">
                <label for="language_group_id">Kelompok Bahasa</label>
                <select id="language_group_id" name="language_group_id">
                    <option value="">Belum memiliki kelompok</option>

                    @foreach ($languageGroups as $group)
                        <option
                            value="{{ $group->id }}"
                            {{ old('language_group_id') == $group->id ? 'selected' : '' }}
                        >
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                @error('language_group_id')
                    <small class="member-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="member-form-group">
                <label for="is_active">Status Pengguna</label>
                <select id="is_active" name="is_active" required>
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                @error('is_active')
                    <small class="member-field-error">{{ $message }}</small>
                @enderror
            </div>

        </div>

        <div class="member-form-actions">
            <a href="{{ route('admin.members.index') }}" class="member-cancel-button">
                Batal
            </a>

            <button type="submit" class="member-add-button">
                Simpan Anggota
            </button>
        </div>

    </form>

</section>

@endsection
