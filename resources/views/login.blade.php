
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login LMS UKM Bahasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">

    <main class="login-container">
        <div class="login-logo">
            <img
                src="{{ asset('assets/images/logo-ukm-bahasa.png') }}"
                alt="Logo UKM Bahasa"
                class="login-logo-image"
            >
        </div>

        <section class="login-card">
            <header class="login-header">
                <h1>Masuk</h1>
                <p>Silakan masuk untuk melanjutkan pembelajaran.</p>
            </header>

            <form action="{{ route('login.process') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-group">
                    <label for="kode_kelompok">Kode Kelompok Bahasa</label>
                    <input
                        type="text"
                        id="kode_kelompok"
                        name="kode_kelompok"
                        value="{{ old('kode_kelompok') }}"
                        placeholder="Contoh: arb001"
                        required
                    >
                </div>

                @if ($errors->any())
                    <p class="login-error">
                        {{ $errors->first() }}
                    </p>
                @endif

                <button type="submit" class="login-button">
                    Masuk
                </button>
            </form>

            <div class="login-help">
                <p>Gunakan akun yang telah terdaftar untuk masuk.</p>
            </div>
        </section>
    </main>

</body>
</html>
