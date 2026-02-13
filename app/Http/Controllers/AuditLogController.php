<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $r)
    {
        $query = AuditLog::with('user:id,name')
            ->when($r->user_id, fn($q) => $q->where('user_id', $r->user_id))
            ->when($r->action, fn($q) => $q->where('action', $r->action))
            ->when($r->model, fn($q) => $q->where('auditable_type', 'App\\Models\\' . $r->model))
            ->when($r->from, fn($q) => $q->whereDate('created_at', '>=', $r->from))
            ->when($r->to, fn($q) => $q->whereDate('created_at', '<=', $r->to))
            ->when($r->search, function($q) use ($r) {
                $q->where('auditable_label', 'like', "%{$r->search}%");
            });

        $logs = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        $users = User::whereIn('role', ['admin', 'asistente', 'odontologo'])
            ->orderBy('name')->get(['id', 'name']);

        $models = [
            'Patient'       => 'Paciente',
            'Appointment'   => 'Cita',
            'Invoice'       => 'Factura',
            'Product'       => 'Producto',
            'Service'       => 'Servicio',
            'Dentist'       => 'Odontólogo',
            'User'          => 'Usuario',
            'TreatmentPlan' => 'Plan de Tratamiento',
        ];

        return view('admin.audit.index', compact('logs', 'users', 'models'));
    }
}
