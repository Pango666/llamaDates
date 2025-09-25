<?php

namespace App\Http\Controllers;

use App\Models\Chair;
use App\Models\Dentist;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $dentists = Dentist::query()
            ->withCount(['schedules as blocks_count'])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('specialty', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // días configurados por odontólogo (para mostrar badges)
        $daysByDentist = Schedule::select('dentist_id', 'day_of_week')
            ->distinct()->get()
            ->groupBy('dentist_id')
            ->map(fn($g) => $g->pluck('day_of_week')->all());

        $dayLabels = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        return view('admin.schedules.index', compact('dentists', 'q', 'daysByDentist', 'dayLabels'));
    }

    /** Editor por odontólogo (bloques por día + pausas) */
    public function edit(Dentist $dentist)
    {
        $existing = Schedule::where('dentist_id', $dentist->id)
            ->orderBy('day_of_week')->orderBy('start_time')->get();

        $byDay = collect(range(0, 6))->mapWithKeys(function ($d) use ($existing) {
            return [$d => $existing->where('day_of_week', $d)->values()];
        });

        $dayLabels = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $chairs    = Chair::orderBy('name')->get();

        // Mapa de sillas disponibles para cada bloque existente (día+index)
        $availMap = [];
        foreach (range(0, 6) as $day) {
            $blocks = $byDay[$day] ?? collect();
            foreach ($blocks as $i => $b) {
                $availMap["$day:$i"] = $this->availableChairIds($day, substr($b->start_time, 0, 5), substr($b->end_time, 0, 5), $dentist->id);
            }
        }

        return view('admin.schedules.edit', compact('dentist', 'byDay', 'dayLabels', 'chairs', 'availMap'));
    }

    // validador de sillas disponibles
    private function availableChairIds(int $day, string $startHHmm, string $endHHmm, int $excludeDentistId): array
    {
        $all = Chair::pluck('id')->all();
        if (!$startHHmm || !$endHHmm) return $all;

        // solape estricto: [A.start < B.end) && (A.end > B.start)
        $conflicting = Schedule::query()
            ->whereNotNull('chair_id')
            ->where('day_of_week', $day)
            ->where('dentist_id', '!=', $excludeDentistId) // mismo dentista no bloquea
            ->where('start_time', '<',  $endHHmm . ':00')
            ->where('end_time',   '>',  $startHHmm . ':00')
            ->pluck('chair_id')
            ->unique()
            ->all();

        return array_values(array_diff($all, $conflicting));
    }

    public function chairOptions(Request $r, Dentist $dentist)
    {
        $data = $r->validate([
            'day'        => 'required|integer|min:0|max:6',
            // acepta HH:MM o HH:MM:SS
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time'   => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        // normaliza a HH:MM
        $startHHmm = substr($data['start_time'], 0, 5);
        $endHHmm   = substr($data['end_time'],   0, 5);

        $ids = $this->availableChairIds((int)$data['day'], $startHHmm, $endHHmm, $dentist->id);

        $list = Chair::orderBy('name')
            ->get(['id', 'name', 'shift'])
            ->map(fn($c) => [
                'id'        => $c->id,
                'name'      => $c->name,
                'shift'     => $c->shift,
                'available' => in_array($c->id, $ids, true),
            ]);

        return response()->json($list);
    }

    public function update(Request $request, Dentist $dentist)
    {
        // 1) LOG: payload completo que llega del form
        \Log::debug('Schedules::update - RAW request', ['all' => $request->all()]);

        $input = $request->input('schedule', []);
        $rows = [];
        $skipped = []; // para no frenar todo
        $dayLabels = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        foreach ($input as $dayStr => $blocks) {
            if (!is_numeric($dayStr)) continue;
            $day = (int)$dayStr;
            if ($day < 0 || $day > 6 || !is_array($blocks)) continue;

            foreach (array_values($blocks) as $idx => $b) {
                $startRaw = $b['start_time'] ?? '';
                $endRaw   = $b['end_time']   ?? '';
                $brStr    = (string)($b['breaks'] ?? '');

                // LOG por bloque (antes de normalizar)
                \Log::debug('Schedules::update - BLOCK RAW', [
                    'day' => $day,
                    'label' => $dayLabels[$day],
                    'idx' => $idx + 1,
                    'start_raw' => $startRaw,
                    'end_raw' => $endRaw,
                    'breaks_raw' => $brStr,
                ]);

                // si ambos vacíos, ignorar sin molestar
                if (trim($startRaw) === '' && trim($endRaw) === '') {
                    \Log::debug('Schedules::update - BLOCK SKIP (empty)', ['day' => $day, 'idx' => $idx + 1]);
                    continue;
                }

                $start = $this->normTime($startRaw);
                $end   = $this->normTime($endRaw);

                // LOG tras normalizar
                \Log::debug('Schedules::update - BLOCK NORM', [
                    'day' => $day,
                    'idx' => $idx + 1,
                    'start_norm' => $start,
                    'end_norm' => $end
                ]);

                if (!$start || !$end) {
                    // en vez de abortar TODO, lo marcamos y seguimos para que puedas guardar el resto
                    $skipped[] = "día {$dayLabels[$day]} (bloque " . ($idx + 1) . ") [start='{$startRaw}', end='{$endRaw}']";
                    \Log::warning('Schedules::update - BLOCK INVALID TIME', [
                        'day' => $day,
                        'idx' => $idx + 1,
                        'start_raw' => $startRaw,
                        'end_raw' => $endRaw
                    ]);
                    continue;
                }
                if ($end <= $start) {
                    $skipped[] = "día {$dayLabels[$day]} (bloque " . ($idx + 1) . "): fin <= inicio";
                    \Log::warning('Schedules::update - BLOCK END<=START', [
                        'day' => $day,
                        'idx' => $idx + 1,
                        'start' => $start,
                        'end' => $end
                    ]);
                    continue;
                }

                // Pausas tolerantes
                $breaks = [];
                $brNorm = str_replace(['–', '—', '−'], '-', $brStr);
                $brNorm = preg_replace('/\s+/', '', $brNorm);
                if ($brNorm !== '') {
                    foreach (explode(',', $brNorm) as $pair) {
                        if ($pair === '') continue;
                        [$bsRaw, $beRaw] = array_pad(explode('-', $pair, 2), 2, null);
                        $bs = $this->normTime($bsRaw);
                        $be = $this->normTime($beRaw);
                        \Log::debug('Schedules::update - BREAK NORM', [
                            'day' => $day,
                            'idx' => $idx + 1,
                            'pair' => $pair,
                            'bs' => $bs,
                            'be' => $be
                        ]);
                        if (!$bs || !$be || $be <= $bs) {
                            $skipped[] = "día {$dayLabels[$day]} (bloque " . ($idx + 1) . "): pausa inválida '{$pair}'";
                            \Log::warning('Schedules::update - BREAK INVALID', [
                                'day' => $day,
                                'idx' => $idx + 1,
                                'pair' => $pair,
                                'bs' => $bs,
                                'be' => $be
                            ]);
                            continue; // solo salta esa pausa, no todo el bloque
                        }
                        $breaks[] = ['start' => $bs, 'end' => $be];
                    }
                }

                // Silla (opcional)
                $chairId = isset($b['chair_id']) && $b['chair_id'] !== '' ? (int)$b['chair_id'] : null;
                if ($chairId !== null) {
                    if (!\App\Models\Chair::whereKey($chairId)->exists()) {
                        $skipped[] = "día {$dayLabels[$day]} (bloque " . ($idx + 1) . "): silla inválida (#{$chairId})";
                        \Log::warning('Schedules::update - INVALID CHAIR', ['day' => $day, 'idx' => $idx + 1, 'chair_id' => $chairId]);
                        // seguimos, pero sin silla
                        $chairId = null;
                    }
                }

                $rows[] = [
                    'dentist_id'  => $dentist->id,
                    'day_of_week' => $day,
                    'start_time'  => $start,        // "HH:MM"
                    'end_time'    => $end,
                    'breaks'      => $breaks ?: null,
                    'chair_id'    => $chairId,      // puede ser null
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // LOG de lo que se insertará
        \Log::debug('Schedules::update - ROWS TO SAVE', ['rows' => $rows, 'skipped' => $skipped]);

        // Solapes por día (solo con lo válido) – dentro del mismo odontólogo
        $byDayCheck = collect($rows)->groupBy('day_of_week');
        foreach ($byDayCheck as $d => $blocks) {
            $sorted = $blocks->sortBy('start_time')->values()->all();
            for ($i = 1; $i < count($sorted); $i++) {
                if ($sorted[$i]['start_time'] < $sorted[$i - 1]['end_time']) {
                    \Log::warning('Schedules::update - OVERLAP (same dentist)', ['day' => $d, 'rows' => $sorted]);
                    return back()->withErrors("Bloques solapados en {$dayLabels[$d]}.")->withInput();
                }
            }
        }

        // Choques de SILLA con otros odontólogos (misma silla, mismo día, rangos solapados)
        foreach ($rows as $i => $row) {
            if (empty($row['chair_id'])) continue;

            $conflict = \App\Models\Schedule::where('chair_id', $row['chair_id'])
                ->where('day_of_week', $row['day_of_week'])
                ->where('dentist_id', '!=', $dentist->id)
                ->where(function ($q) use ($row) {
                    // solape: startA < endB && endA > startB
                    $q->where('start_time', '<', $row['end_time'])
                        ->where('end_time',   '>', $row['start_time']);
                })
                ->orderBy('start_time')
                ->first();

            if ($conflict) {
                $chairName = optional(\App\Models\Chair::find($row['chair_id']))->name ?: ('Silla #' . $row['chair_id']);
                $dayName   = $dayLabels[(int)$row['day_of_week']] ?? (string)$row['day_of_week'];
                $otherDoc  = optional(\App\Models\Dentist::find($conflict->dentist_id))->name ?: 'otro odontólogo';

                $msg = "Conflicto de silla: {$chairName} ya está asignada a {$otherDoc} el {$dayName} " .
                    "({$conflict->start_time}–{$conflict->end_time}), se solapa con tu bloque " .
                    "{$row['start_time']}–{$row['end_time']}.";
                \Log::warning('Schedules::update - CHAIR CONFLICT', [
                    'this_row' => $row,
                    'conflict' => $conflict->toArray(),
                    'message'  => $msg,
                ]);
                return back()->withErrors($msg)->withInput();
            }
        }

        // Persistencia
        \DB::transaction(function () use ($dentist, $rows) {
            \App\Models\Schedule::where('dentist_id', $dentist->id)->delete();
            if ($rows) {
                foreach (array_chunk($rows, 500) as $chunk) {
                    \App\Models\Schedule::insert($chunk);
                }
            }
        });

        // Si hubo bloques/pausas saltados, avisamos pero guardamos lo demás
        if (!empty($skipped)) {
            return redirect()
                ->route('admin.schedules.edit', $dentist)
                ->with('warn', 'Algunos bloques/pausas fueron omitidos: ' . implode('; ', $skipped));
        }

        return redirect()->route('admin.schedules.edit', $dentist)->with('ok', 'Horarios actualizados.');
    }




    private function dayName(int $d): string
    {
        return ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'][$d] ?? (string)$d;
    }

    /** Normaliza horas variadas a 'HH:MM' (24h). Devuelve null si no puede. */
    private function normTime(?string $s): ?string
    {
        if ($s === null) return null;
        $s = trim((string)$s);
        if ($s === '') return null;

        // 09:00  ó 9:00
        if (preg_match('/^([01]?\d|2[0-3]):[0-5]\d$/', $s)) {
            // si viene "9:00" -> "09:00"
            [$h, $m] = explode(':', $s);
            return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $m;
        }

        // 09:00:00
        if (preg_match('/^([01]?\d|2[0-3]):[0-5]\d:[0-5]\d$/', $s)) {
            return substr($s, 0, 5);
        }

        // Formatos con AM/PM o similares => strtotime
        $ts = @strtotime($s);
        if ($ts !== false) {
            return date('H:i', $ts);
        }

        return null;
    }
}
