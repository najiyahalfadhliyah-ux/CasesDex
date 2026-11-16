<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogFilterRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\View\View;

// Riwayat aktivitas: hanya baca (append-only). Tidak ada create/edit/delete lewat route.
class AuditLogController extends Controller
{
    public function index(AuditLogFilterRequest $request): View
    {
        $filters = $request->validated();

        $logs = AuditLog::with('user')
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest('created_at')->latest('id') // id: urutan pasti untuk catatan di detik yang sama
            ->paginate(25)
            ->withQueryString();

        return view('audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'users' => User::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
