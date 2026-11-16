<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Services\CaseStatistics;
use Illuminate\View\View;

// Statistik dan laporan ringkas (dicetak lewat fitur cetak browser). Hak akses sama dengan dashboard.
class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, CaseStatistics $statistics): View
    {
        $filters = $request->validated();

        return view('reports.index', [
            'stats' => $statistics->summarize($statistics->base($request->user(), $filters['from'] ?? null, $filters['to'] ?? null)),
            'filters' => $filters,
        ]);
    }
}
