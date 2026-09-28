<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->string('action')->toString();

        $query = AuditLog::query()->with('actor')->orderByDesc('created_at')->orderByDesc('id');

        if ($action !== '') {
            $query->where('action', $action);
        }

        return view('admin.audit-logs.index', [
            'logs' => $query->paginate(20)->withQueryString(),
            'action' => $action,
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
