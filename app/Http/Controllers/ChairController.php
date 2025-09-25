<?php

namespace App\Http\Controllers;

use App\Models\Chair;
use App\Models\Dentist;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChairController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $chairs = Chair::when($q, fn($qq) =>
        $qq->where('name', 'like', "%{$q}%")
            ->orWhere('shift', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.chairs.index', compact('chairs', 'q'));
    }

    public function create()
    {
        $chair = new Chair(['shift' => 'completo']);
        return view('admin.chairs.create', compact('chair'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'  => ['required', 'string', 'max:120'],
            'shift' => ['required', 'in:mañana,tarde,completo'],
        ]);

        Chair::create($data);
        return redirect()->route('admin.chairs.index')->with('ok', 'Silla creada.');
    }

    public function edit(Chair $chair)
    {
        return view('admin.chairs.edit', compact('chair'));
    }

    public function update(Request $r, Chair $chair)
    {
        $data = $r->validate([
            'name'  => ['required', 'string', 'max:120'],
            'shift' => ['required', 'in:mañana,tarde,completo'],
        ]);

        $chair->update($data);
        return redirect()->route('admin.chairs.index')->with('ok', 'Silla actualizada.');
    }

    public function destroy(Chair $chair)
    {
        // Bloquea borrar si la silla está en uso por horarios o dentistas
        $inUse = Schedule::where('chair_id', $chair->id)->exists()
            || Dentist::where('chair_id', $chair->id)->exists();

        if ($inUse) {
            return back()->withErrors('No se puede eliminar: la silla está asignada en horarios o a un odontólogo.');
        }

        $chair->delete();
        return back()->with('ok', 'Silla eliminada.');
    }

    public function usageByWeekday()
    {
        // Trae todas las sillas
        $chairs = Chair::orderBy('name')->get(['id', 'name', 'shift']);

        // Schedules con silla + dentista (solo los que tienen chair_id seteado)
        $scheds = Schedule::with(['dentist:id,name', 'chair:id,name'])
            ->whereNotNull('chair_id')
            ->orderBy('day_of_week')
            ->orderBy('chair_id')
            ->orderBy('start_time')
            ->get(['id', 'dentist_id', 'chair_id', 'day_of_week', 'start_time', 'end_time']);

        // Agrupar por día de semana y por silla
        $byDay = collect(range(0, 6))->mapWithKeys(function ($d) use ($scheds) {
            $rows = $scheds->where('day_of_week', $d)->groupBy('chair_id')->map(function ($g) {
                return $g->map(function ($s) {
                    return [
                        'chair_id'   => $s->chair_id,
                        'chair_name' => $s->chair?->name,
                        'dentist'    => $s->dentist?->name ?? '—',
                        'start'      => substr($s->start_time, 0, 5),
                        'end'        => substr($s->end_time, 0, 5),
                    ];
                })->values();
            });
            return [$d => $rows];
        });

        $dayLabels = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        return view('admin.chairs.usage', compact('chairs', 'byDay', 'dayLabels'));
    }
}
