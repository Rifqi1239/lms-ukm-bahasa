<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Modul terbaru sesuai kelompok bahasa pengguna
        $modules = Module::query()
            ->where('language_group_id', $user->language_group_id)
            ->with('languageGroup')
            ->withCount('questions')
            ->latest()
            ->take(3)
            ->get();

        // Progres membaca terakhir milik pengguna
        $continueProgress = ReadingProgress::query()
            ->with('module.languageGroup')
            ->where('user_id', $user->id)
            ->whereHas('module', function ($query) use ($user) {
                $query->where(
                    'language_group_id',
                    $user->language_group_id
                );
            })
            ->latest('last_read_at')
            ->first();

        $continueModule = $continueProgress?->module;

        return view('dashboard', compact(
            'modules',
            'continueModule',
            'continueProgress'
        ));
    }
}
