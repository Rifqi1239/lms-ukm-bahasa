<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LanguageGroup;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // DASHBOARD ADMIN
        if ($user->role === 'admin') {

            $totalUsers = User::count();

            $totalLanguageGroups = LanguageGroup::count();

            $totalModules = Module::count();

            $totalQuestions = Question::count();

        } else {

            // DASHBOARD PENGAJAR
            $groupId = $user->language_group_id;

            // Jumlah anggota dalam kelompok pengajar
            $totalUsers = $groupId
                ? User::where('role', 'anggota')
                    ->where('language_group_id', $groupId)
                    ->count()
                : 0;

            // Kelompok bahasa yang ditangani pengajar
            $totalLanguageGroups = $groupId
                ? LanguageGroup::where('id', $groupId)->count()
                : 0;

            // Modul dalam kelompok pengajar
            $totalModules = $groupId
                ? Module::where('language_group_id', $groupId)->count()
                : 0;

            // Jumlah soal dari modul kelompok pengajar
            $totalQuestions = $groupId
                ? Module::where('language_group_id', $groupId)
                    ->withCount('questions')
                    ->get()
                    ->sum('questions_count')
                : 0;
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalLanguageGroups',
            'totalModules',
            'totalQuestions'
        ));
    }
}
