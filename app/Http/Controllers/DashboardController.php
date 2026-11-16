<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Services\CaseStatistics;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Dashboard: angka dan grafik dihitung dari kasus yang boleh dilihat pengguna (lihat CaseStatistics)
class DashboardController extends Controller
{
    public function index(Request $request, CaseStatistics $statistics): View
    {
        $user = $request->user();

        return view('dashboard', [
            'stats' => $statistics->summarize($statistics->base($user)),
            'reminders' => $statistics->nearArchiveDeadline($user),
            'latest' => CaseRecord::visibleTo($user)->with('officer')->latest()->latest('id')->limit(5)->get(),
        ]);
    }
}
