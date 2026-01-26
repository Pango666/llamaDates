<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\EmailLog;

class EmailLogController extends Controller
{
    public function index()
    {
        $logs = EmailLog::latest()->paginate(20);
        return view('admin.emails.index_logs', compact('logs'));
    }

    public function show(EmailLog $emailLog)
    {
        return view('admin.emails.show_log', compact('emailLog')); // Optional, or just show in modal
    }
}
