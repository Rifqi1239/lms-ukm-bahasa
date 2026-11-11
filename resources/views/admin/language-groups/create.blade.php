
@extends('layouts.admin')

@section('page_title', 'Tambah Kelompok Bahasa')
@section('breadcrumb', 'Halaman / Kelompok Bahasa / Tambah')

@section('content')

    <div class="member-page-header">
        <div>
            <h1>Tambah Kelompok Bahasa</h1>
            <p>Tambahkan kelompok bahasa baru ke dalam LMS UKM Bahasa.</p>
        </div>

        <a href="{{ route('admin.language-groups.index') }}"
            class="member-add-button">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="member-alert error">
            <strong>Periksa kembali data yang dimasukkan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="member-table-card language-form-card">
        <div class="member-table-header">
            <div class="member-table-title">
                <div class="member-table-title-icon">＋</div>
                <div>
                    <h2>Informasi Kelompok Bahasa</h2>
                    <p>Lengkapi informasi kelompok bahasa berikut.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.language-groups.store') }}"
            method="POST"
            class="language-group-form">

            @csrf

            <div class="language-form-group">
                <label for="name">Nama Kelompok Bahasa</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Contoh: Bahasa Inggris"
                    required
                >
            </div>

            <div class="language-form-group">
                <label for="code">Kode Bahasa</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code') }}"
                    placeholder="Contoh: EN"
                    maxlength="20"
                    required
                >
                <small>
                    Gunakan kode singkat dan unik, misalnya EN, AR, TR, KR, atau JP.
                </small>
            </div>

            <div class="language-form-group">
                <label for="description">Deskripsi</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Tuliskan deskripsi kelompok bahasa..."
                >{{ old('description') }}</textarea>
            </div>

            <div class="language-form-actions">
                <a href="{{ route('admin.language-groups.index') }}"
                    class="language-cancel-button">
                    Batal
                </a>

                <button type="submit" class="member-add-button">
                    Simpan Kelompok
                </button>
            </div>
        </form>
    </section>

@endsection

@push('styles')
<style>
    .language-form-card {
        padding-bottom: 25px;
    }

    .language-group-form {
        padding: 25px;
    }

    .language-form-group {
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin-bottom: 22px;
    }

    .language-form-group label {
        font-size: 14px;
        font-weight: 600;
        color: #263445;
    }

    .language-form-group input,
    .language-form-group textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #DCE2EA;
        border-radius: 8px;
        background: #fff;
        color: #263445;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .language-form-group input:focus,
    .language-form-group textarea:focus {
        border-color: #4776E6;
        box-shadow: 0 0 0 3px rgba(71, 118, 230, .10);
    }

    .language-form-group textarea {
        resize: vertical;
        min-height: 110px;
    }

    .language-form-group small {
        color: #7B8794;
        font-size: 12px;
    }

    .language-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 30px;
    }

    .language-cancel-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 20px;
        border: 1px solid #DCE2EA;
        border-radius: 8px;
        color: #526071;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .language-cancel-button:hover {
        background: #F5F7FA;
    }

    @media (max-width: 600px) {
        .language-group-form {
            padding: 18px;
        }

        .language-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .language-form-actions a,
        .language-form-actions button {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endpush
