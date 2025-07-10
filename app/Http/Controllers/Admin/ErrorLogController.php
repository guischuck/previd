<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ErrorLogController extends Controller
{
    public function index()
    {
        $logs = ErrorLog::with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/ErrorLogs/Index', [
            'logs' => $logs
        ]);
    }

    public function show(ErrorLog $errorLog)
    {
        $errorLog->load('user');
        
        return Inertia::render('Admin/ErrorLogs/Show', [
            'log' => $errorLog
        ]);
    }

    public function markAsResolved(ErrorLog $errorLog, Request $request)
    {
        $request->validate([
            'resolution_notes' => 'nullable|string|max:1000'
        ]);

        $errorLog->update([
            'resolved' => true,
            'resolved_at' => now(),
            'resolution_notes' => $request->resolution_notes
        ]);

        return back()->with('success', 'Erro marcado como resolvido.');
    }

    public function destroy(ErrorLog $errorLog)
    {
        $errorLog->delete();
        return back()->with('success', 'Log de erro excluído com sucesso.');
    }
} 