<?php

namespace App\Http\Controllers;

use App\Models\FinalGradeRecord;
use Illuminate\Support\Facades\Auth;

class MemberFinalGradeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Halaman ini hanya untuk anggota
        abort_unless($user->role === 'anggota', 403);

        // Ambil penilaian terakhir yang sudah disetujui pengajar
        $record = FinalGradeRecord::where('user_id', $user->id)
            ->where('status', 'approved')
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->first();

        $moduleScores = [];

        if ($record) {
            $moduleScores = $record->module_scores ?? [];

            // Antisipasi jika JSON belum dikonversi menjadi array
            if (is_string($moduleScores)) {
                $moduleScores = json_decode($moduleScores, true) ?? [];
            }
        }

        return view('member.final-grades.index', compact(
            'record',
            'moduleScores'
        ));
    }
}
