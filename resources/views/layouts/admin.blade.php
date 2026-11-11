
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard Admin | LMS UKM Bahasa')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="lms-page admin-page">

    <div class="admin-layout">

        <!-- SIDEBAR -->
        @include('components.admin.sidebar')

        <!-- AREA UTAMA -->
        <div class="admin-main">

            <!-- TOPBAR -->
            <header class="admin-topbar">

                <div>
                    <span class="admin-breadcrumb">
                        @yield('breadcrumb', 'Halaman / Dashboard')
                    </span>

                    <h2>
                        @yield('page-title', 'Dashboard')
                    </h2>
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

            <!-- KONTEN HALAMAN -->
            <main class="admin-content">

                @yield('content')

                <!-- FOOTER -->
                <footer class="admin-footer">
                    LMS UKM Bahasa &copy; {{ date('Y') }}
                </footer>

            </main>

        </div>

    </div>

    @stack('scripts')

</body>

</html>
