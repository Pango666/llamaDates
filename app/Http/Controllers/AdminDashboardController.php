<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\{Appointment, Patient, Dentist, Service};
use Illuminate\Support\Facades\View;

class AdminDashboardController extends Controller
{
     public function index(Request $request)
    {
        $today = Carbon::today();
        $month = Carbon::parse($request->get('month', $today->format('Y-m'))); // YYYY-MM
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $stats = [
            'patients'    => Patient::count(),
            'dentists'    => Dentist::count(),
            'services'    => Service::count(),
            'todayVisits' => Appointment::whereDate('date', $today)->count(),
        ];

        $day = Carbon::parse($request->get('day', $today->toDateString()));

        $appointments = Appointment::with(['patient:id,first_name,last_name', 'service:id,name'])
            ->whereDate('date', $day)
            ->orderBy('start_time')
            ->get();

        $perDay = Appointment::selectRaw('date, COUNT(*) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('date')
            ->pluck('total','date'); // ['YYYY-MM-DD' => n]

        return view('admin.dashboard', compact('stats','month','day','appointments','perDay'));
    }

    // AJAX: devuelve HTML de los parciales para refrescar sin recargar página
    public function data(Request $request)
    {
        $month = Carbon::parse($request->get('month', now()->format('Y-m')));
        $day   = Carbon::parse($request->get('day',   now()->toDateString()));

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $appointments = Appointment::with(['patient:id,first_name,last_name', 'service:id,name'])
            ->whereDate('date', $day)
            ->orderBy('start_time')
            ->get();

        $perDay = Appointment::selectRaw('date, COUNT(*) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('date')
            ->pluck('total','date');

        $calendarHtml = View::make('admin.partials._calendar', compact('month','day','perDay'))->render();
        $listHtml     = View::make('admin.partials._day_list', compact('day','appointments'))->render();

        return response()->json([
            'calendar'    => $calendarHtml,
            'day_list'    => $listHtml,
            'month_label' => $month->translatedFormat('F Y'),
        ]);
    }
}
