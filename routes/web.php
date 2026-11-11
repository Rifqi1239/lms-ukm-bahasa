
<?php

use Illuminate\Support\Facades\Route;

// Controller Autentikasi dan Anggota
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\MemberFinalGradeController;

// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\LanguageGroupController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\LearningResultController;
use App\Http\Controllers\Admin\FinalGradeController;


/*
|--------------------------------------------------------------------------
| HALAMAN AWAL
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| AUTENTIKASI
|--------------------------------------------------------------------------
*/

// Form login
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

// Proses login
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| DASHBOARD ANGGOTA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');

});


/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN DAN PENGAJAR
|--------------------------------------------------------------------------
*/

// Dashboard bersama admin dan pengajar
Route::get('/admin/dashboard', [
    AdminDashboardController::class,
    'index'
])
    ->middleware(['auth', 'role:admin,pengajar'])
    ->name('admin.dashboard');

// Alamat dashboard lama pengajar diarahkan ke dashboard bersama
Route::get('/dashboard-pengajar', function () {
    return redirect()->route('admin.dashboard');
})
    ->middleware(['auth', 'role:pengajar'])
    ->name('dashboard.pengajar');


/*
|--------------------------------------------------------------------------
| MANAJEMEN PENGGUNA
|--------------------------------------------------------------------------
| Hanya admin yang dapat mengelola akun pengguna.
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/members', [
            MemberController::class,
            'index'
        ])->name('members.index');

        Route::get('/members/create', [
            MemberController::class,
            'create'
        ])->name('members.create');

        Route::post('/members', [
            MemberController::class,
            'store'
        ])->name('members.store');

        Route::get('/members/{member}/edit', [
            MemberController::class,
            'edit'
        ])->name('members.edit');

        Route::put('/members/{member}', [
            MemberController::class,
            'update'
        ])->name('members.update');

        Route::delete('/members/{member}', [
            MemberController::class,
            'destroy'
        ])->name('members.destroy');

    });


/*
|--------------------------------------------------------------------------
| KELOMPOK BAHASA
|--------------------------------------------------------------------------
| Hanya admin yang dapat mengelola kelompok bahasa.
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/language-groups', [
            LanguageGroupController::class,
            'index'
        ])->name('language-groups.index');

        Route::get('/language-groups/create', [
            LanguageGroupController::class,
            'create'
        ])->name('language-groups.create');

        Route::post('/language-groups', [
            LanguageGroupController::class,
            'store'
        ])->name('language-groups.store');

        Route::get('/language-groups/{languageGroup}/edit', [
            LanguageGroupController::class,
            'edit'
        ])->name('language-groups.edit');

        Route::put('/language-groups/{languageGroup}', [
            LanguageGroupController::class,
            'update'
        ])->name('language-groups.update');

        Route::patch('/language-groups/{languageGroup}/toggle-status', [
            LanguageGroupController::class,
            'toggleStatus'
        ])->name('language-groups.toggle-status');

    });


/*
|--------------------------------------------------------------------------
| KELOLA MODUL
|--------------------------------------------------------------------------
| Admin dan pengajar dapat mengelola modul.
| Pembatasan kelompok bahasa dilakukan di controller.
*/

Route::middleware(['auth', 'role:admin,pengajar'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/modules', [
            AdminModuleController::class,
            'index'
        ])->name('modules.index');

        Route::get('/modules/create', [
            AdminModuleController::class,
            'create'
        ])->name('modules.create');

        Route::post('/modules', [
            AdminModuleController::class,
            'store'
        ])->name('modules.store');

        Route::get('/modules/{module}/edit', [
            AdminModuleController::class,
            'edit'
        ])->name('modules.edit');

        Route::get('/modules/{module}/pdf', [
            AdminModuleController::class,
            'pdf'
        ])->name('modules.pdf');

        Route::put('/modules/{module}', [
            AdminModuleController::class,
            'update'
        ])->name('modules.update');

        Route::delete('/modules/{module}', [
            AdminModuleController::class,
            'destroy'
        ])->name('modules.destroy');

    });


/*
|--------------------------------------------------------------------------
| KELOLA SOAL LATIHAN
|--------------------------------------------------------------------------
| Admin dan pengajar dapat mengelola soal latihan.
| Pembatasan kelompok bahasa dilakukan di controller.
*/

Route::middleware(['auth', 'role:admin,pengajar'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/questions', [
            QuestionController::class,
            'index'
        ])->name('questions.index');

        Route::get('/questions/create', [
            QuestionController::class,
            'create'
        ])->name('questions.create');

        Route::post('/questions', [
            QuestionController::class,
            'store'
        ])->name('questions.store');

        Route::get('/questions/{question}/edit', [
            QuestionController::class,
            'edit'
        ])->name('questions.edit');

        Route::put('/questions/{question}', [
            QuestionController::class,
            'update'
        ])->name('questions.update');

        Route::delete('/questions/{question}', [
            QuestionController::class,
            'destroy'
        ])->name('questions.destroy');

    });


/*
|--------------------------------------------------------------------------
| HASIL BELAJAR DAN PENILAIAN AKHIR
|--------------------------------------------------------------------------
| Admin dapat melihat semua kelompok.
| Pengajar hanya boleh melihat kelompok yang ditugaskan.
*/

Route::middleware(['auth', 'role:admin,pengajar'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Hasil belajar
        Route::get('/learning-results', [
            LearningResultController::class,
            'index'
        ])->name('learning-results.index');

        // Daftar penilaian akhir
        Route::get('/final-grades', [
            FinalGradeController::class,
            'index'
        ])->name('final-grades.index');

        // Menghitung nilai akhir
        Route::post('/final-grades/calculate', [
            FinalGradeController::class,
            'calculate'
        ])->name('final-grades.calculate');

        // Menyetujui nilai akhir
        Route::post('/final-grades/{record}/approve', [
            FinalGradeController::class,
            'approve'
        ])->name('final-grades.approve');

    });


/*
|--------------------------------------------------------------------------
| PENILAIAN AKHIR ANGGOTA
|--------------------------------------------------------------------------
*/

Route::get('/penilaian-akhir', [
    MemberFinalGradeController::class,
    'index'
])
    ->middleware(['auth', 'role:anggota'])
    ->name('member.final-grades.index');


/*
|--------------------------------------------------------------------------
| MODUL PEMBELAJARAN ANGGOTA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Menyimpan progres membaca
    Route::post('/modules/{module}/progress', [
        ModuleController::class,
        'saveProgress'
    ])->name('modules.progress.store');

    // Daftar modul
    Route::get('/modules', [
        ModuleController::class,
        'index'
    ])->name('modules.index');

    // Halaman deskripsi modul
    Route::get('/modules/{module}', [
        ModuleController::class,
        'show'
    ])->name('modules.show');

    // Halaman membaca PDF
    Route::get('/modules/{module}/read', [
        ModuleController::class,
        'read'
    ])->name('modules.read');

    // Menampilkan PDF
    Route::get('/modules/{module}/pdf-view', [
        ModuleController::class,
        'streamPdf'
    ])->name('modules.pdf-view');

});


/*
|--------------------------------------------------------------------------
| LATIHAN ANGGOTA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Riwayat latihan
    Route::get('/quiz/history', [
        QuizController::class,
        'history'
    ])->name('quiz.history');

    // Memulai atau melanjutkan latihan
    Route::get('/modules/{module}/quiz/start', [
        QuizController::class,
        'start'
    ])->name('quiz.start');

    // Menampilkan soal
    Route::get('/quiz/{attempt}', [
        QuizController::class,
        'show'
    ])->name('quiz.show');

    // Menyimpan jawaban
    Route::post('/quiz/{attempt}/answer', [
        QuizController::class,
        'saveAnswer'
    ])->name('quiz.answer');

    // Mengirim latihan
    Route::post('/quiz/{attempt}/submit', [
        QuizController::class,
        'submit'
    ])->name('quiz.submit');

    // Menampilkan hasil latihan
    Route::get('/quiz/{attempt}/result', [
        QuizController::class,
        'result'
    ])->name('quiz.result');

});
