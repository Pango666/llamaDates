<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Chair;
use App\Models\Dentist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DentistController extends Controller
{
    public function index(Request $request)
    {
        // Filtros llegados por GET
        $q         = trim((string)$request->get('q', ''));
        $specialty = $request->get('specialty');        // literal
        $status    = $request->get('status');           // 'active' | 'inactive' | null
        $chairId   = $request->get('chair');            // id numérico o null

        // Base query
        $dentists = Dentist::query()
            ->with(['chair:id,name']);

        // Texto libre (nombre / email / especialidad)
        if ($q !== '') {
            $dentists->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('specialty', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Especialidad literal (si tu columna specialty está tal cual)
        if ($specialty !== null && $specialty !== '') {
            $dentists->where('specialty', $specialty);
        }

        
        if (in_array($status, ['active', 'inactive'], true)) {
            $flag = $status === 'active' ? 1 : 0;
            $dentists->where('status', $flag); 
        }

        // Sillón (exacto)
        if (!empty($chairId)) {
            $dentists->where('chair_id', $chairId);
        }

        // Orden y paginación
        $dentists = $dentists
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Listado de sillones para el filtro
        $chairs = Chair::orderBy('name')->get(['id', 'name']);

        // Conteo de próximas citas por odontólogo (desde hoy) para la grilla
        $nextCounts = Appointment::selectRaw('dentist_id, COUNT(*) as c')
            ->whereDate('date', '>=', now()->toDateString())
            ->groupBy('dentist_id')
            ->pluck('c', 'dentist_id');

        // Totales reales (no por página)
        $totals = [
            'active'     => Dentist::where('status', 1)->count(), 
            'with_chair' => Dentist::whereNotNull('chair_id')->count(),
        ];

        return view('admin.dentists.index', compact(
            'dentists',
            'q',
            'specialty',
            'status',
            'chairId',
            'chairs',
            'nextCounts',
            'totals'
        ));
    }




    /** Form crear */
    public function create()
    {
        $dentist = new Dentist();
        $chairs  = Chair::orderBy('name')->get(['id', 'name']);

        // Usuarios con rol odontólogo, sin dentista asignado
        // (si tu User no tiene ->dentist(), quita el whereDoesntHave)
        $users   = User::where('role', 'odontologo')
            ->whereDoesntHave('dentist')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.dentists.create', compact('dentist', 'chairs', 'users'));
    }

    /** Guardar (soporta vincular usuario existente o crear uno nuevo) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'chair_id'  => ['nullable', 'exists:chairs,id'],

            // A) vincular existente
            'user_id'   => ['nullable', 'exists:users,id'],

            // B) crear nuevo
            'create_user'       => ['nullable', 'boolean'],
            'new_user_name'     => ['nullable', 'string', 'max:150'],
            'new_user_email'    => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'new_user_password' => ['nullable', 'string', 'min:6'],
        ]);

        // Resolver usuario
        $userId = $data['user_id'] ?? null;

        if (!empty($data['create_user'])) {
            $request->validate([
                'new_user_name'     => ['required', 'string', 'max:150'],
                'new_user_email'    => ['required', 'email', 'max:150', 'unique:users,email'],
                'new_user_password' => ['required', 'string', 'min:6'],
            ]);

            $user = User::create([
                'name'     => $request->new_user_name,
                'email'    => $request->new_user_email,
                'password' => Hash::make($request->new_user_password),
                'role'     => 'odontologo',
                'status'   => 'active',
            ]);
            $userId = $user->id;
        } elseif ($userId) {
            // Si vino user_id, debe ser rol odontólogo
            abort_unless(
                User::where('id', $userId)->where('role', 'odontologo')->exists(),
                422,
                'El usuario debe tener rol odontólogo.'
            );
        }

        $dentist = Dentist::create([
            'name'      => $data['name'],
            'specialty' => $data['specialty'] ?? null,
            'chair_id'  => $data['chair_id'] ?? null,
            'user_id'   => $userId,
        ]);

        return redirect()->route('admin.dentists.show', $dentist)->with('ok', 'Odontólogo creado.');
    }

    /** Perfil */
    public function show(Dentist $dentist)
    {
        $dentist->load(['chair:id,name', 'user:id,name,email']);

        $upcoming = Appointment::with(['patient:id,first_name,last_name', 'service:id,name'])
            ->where('dentist_id', $dentist->id)
            ->whereDate('date', '>=', Carbon::today()->toDateString())
            ->orderBy('date')->orderBy('start_time')
            ->limit(10)
            ->get();

        return view('admin.dentists.show', compact('dentist', 'upcoming'));
    }

    /** Form editar */
    public function edit(Dentist $dentist)
    {
        $chairs = Chair::orderBy('name')->get(['id', 'name']);

        $users = User::where('role', 'odontologo')
            ->where(function ($w) use ($dentist) {
                $w->whereDoesntHave('dentist')
                    ->orWhere('id', $dentist->user_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.dentists.edit', compact('dentist', 'chairs', 'users'));
    }

    /** Actualizar */
    public function update(Request $request, Dentist $dentist)
    {

        $base = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'chair_id'  => ['nullable', 'exists:chairs,id'],
        ]);



        $mode = (string)$request->input('create_user', '0');


        if (!in_array($mode, ['0', '1', 'none'], true)) {
            $mode = '0';
        }

        $userId = $dentist->user_id;

        try {
            if ($mode === '1') {

                $request->validate([
                    'new_user_name'     => ['required', 'string', 'max:150'],
                    'new_user_email'    => ['required', 'email', 'max:150', 'unique:users,email'],
                    'new_user_password' => ['required', 'string', 'min:6'],
                ]);

                $user = \App\Models\User::create([
                    'name'     => $request->new_user_name,
                    'email'    => $request->new_user_email,
                    'password' => Hash::make($request->new_user_password),
                    'role'     => 'odontologo',
                    'status'   => 'active',
                ]);

                $userId = $user->id;
            } elseif ($mode === '0') {

                $incomingUserId = $request->input('user_id');
                if ($incomingUserId) {

                    $okRole = User::where('id', $incomingUserId)
                        ->where('role', 'odontologo')
                        ->exists();

                    if (!$okRole) {
                        return back()->withErrors('El usuario seleccionado no tiene rol "odontólogo".')->withInput();
                    }


                    $yaVinculado = Dentist::where('user_id', $incomingUserId)
                        ->where('id', '!=', $dentist->id)
                        ->exists();

                    if ($yaVinculado) {
                        return back()->withErrors('Ese usuario ya está vinculado a otro odontólogo.')->withInput();
                    }

                    $userId = (int)$incomingUserId;
                }
            } else {

                $userId = null;
            }


            $dentist->update([
                'name'      => $base['name'],
                'specialty' => $base['specialty'] ?? null,
                'chair_id'  => $base['chair_id'] ?? null,
                'user_id'   => $userId,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {

            throw $ve;
        } catch (\Throwable $e) {

            return back()->withErrors($e->getMessage())->withInput();
        }

        return redirect()
            ->route('admin.dentists.show', $dentist)
            ->with('ok', 'Odontólogo actualizado.');
    }


    /** Eliminar (bloquea si tiene citas futuras) */
    public function destroy(Dentist $dentist)
    {
        $hasFuture = Appointment::where('dentist_id', $dentist->id)
            ->whereDate('date', '>=', Carbon::today()->toDateString())
            ->exists();

        if ($hasFuture) {
            return back()->withErrors('No se puede eliminar: tiene citas futuras.');
        }

        $dentist->delete();

        return redirect()->route('admin.dentists')->with('ok', 'Odontólogo eliminado.');
    }
}
