<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Chair;
use App\Models\Dentist;
use App\Models\User;
use App\Services\Mailer\LlamaMailer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class DentistController extends Controller
{
    public function index(Request $request)
    {
        $q         = trim((string)$request->get('q', ''));
        $specialty = $request->get('specialty');      // literal
        $status    = $request->get('status');         // 'active' | 'inactive' | null
        $chairId   = $request->get('chair');          // id o null

        $dentists = Dentist::query()
            ->with(['chair:id,name', 'user:id,name,email']);


        if ($q !== '') {
            $dentists->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('specialty', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('email', 'like', "%{$q}%");
                    });
            });
        }

        // Filtro de especialidad (literal)
        if ($specialty !== null && $specialty !== '') {
            $dentists->where('specialty', $specialty);
        }

        // Filtro de estado (solo 2: active/inactive → 1/0)
        if (in_array($status, ['active', 'inactive'], true)) {
            $dentists->where('status', $status === 'active' ? 1 : 0);
        }

        // Filtro de sillón
        if (!empty($chairId)) {
            $dentists->where('chair_id', $chairId);
        }

        $dentists = $dentists->orderBy('name')->paginate(10)->withQueryString();

        $chairs = Chair::orderBy('name')->get(['id', 'name']);

        $nextCounts = Appointment::selectRaw('dentist_id, COUNT(*) as c')
            ->whereDate('date', '>=', now()->toDateString())
            ->groupBy('dentist_id')
            ->pluck('c', 'dentist_id');

        $totals = [
            'total'      => Dentist::count(),
            'active'     => Dentist::where('status', 1)->count(),
            'inactive'   => Dentist::where('status', 0)->count(),
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
            'ci'        => ['required', 'string', 'unique:dentists'],
            'address'   => ['nullable', 'string', 'max:255'],

            // A) vincular existente
            'user_id'   => ['nullable', 'exists:users,id'],

            // B) crear nuevo
            'create_user'       => ['nullable', 'boolean'],
            'new_user_name'     => ['nullable', 'string', 'max:150'],
            'new_user_email'    => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'new_user_password' => ['nullable', 'string', 'min:6'],
            'new_user_phone'    => ['nullable', 'string', 'max:30'],
            'send_welcome_email' => ['nullable', 'boolean'],
        ]);

        // Resolver usuario
        $userId = $data['user_id'] ?? null;
        $createdUser = null;

        if (!empty($data['create_user'])) {
            $request->validate([
                'new_user_name'     => ['required', 'string', 'max:150'],
                'new_user_email'    => ['required', 'email', 'max:150', 'unique:users,email'],
                'new_user_password' => ['required', 'string', 'min:6'],
            ]);

            $createdUser = User::create([
                'name'     => $request->new_user_name,
                'email'    => $request->new_user_email,
                'password' => Hash::make($request->new_user_password ?: str()->random(16)),
                'phone'    => $request->new_user_phone,
                'role'     => 'odontologo',
                'status'   => 'active',
            ]);

            $userId = $createdUser->id;
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
            'ci'        => $data['ci'],
            'address'   => $data['address'] ?? null,
            'specialty' => $data['specialty'] ?? null,
            'chair_id'  => $data['chair_id'] ?? null,
            'user_id'   => $userId,
            'status'    => 1,
        ]);

        if ($createdUser && $request->boolean('send_welcome_email')) {
            // Genera link seguro para definir/actualizar contraseña
            $token = Password::createToken($createdUser);
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $createdUser->email,
            ]);

            // Payload para la plantilla genérica
            $payload = [
                'subject'     => 'Bienvenido a LlamaDates',
                'brand'       => 'LlamaDates',
                'preheader'   => 'Tu acceso como odontólogo está listo',
                'title'       => '¡Tu cuenta ha sido creada!',
                'subtitle'    => 'Has sido registrado en el sistema como odontólogo.',
                'banner_url'  => 'https://tus-assets/llamadates-banner.png', // opcional
                'image_url'   => null, // si quieres mostrar una imagen aparte
                'text'        => 'Desde tu cuenta podrás gestionar tus citas, pacientes y horarios.',
                'details'     => [
                    'Nombre: <strong>' . e($createdUser->name) . '</strong>',
                    'Correo: <strong>' . e($createdUser->email) . '</strong>',
                ],
                'button_text' => 'Establecer/Actualizar contraseña',
                'button_url'  => $resetUrl,
                'footer'      => 'Si no solicitaste esta cuenta, ignora este mensaje.',
            ];

            // Envía (puedes cambiar a ->queue en producción)
            app(LlamaMailer::class)->send($createdUser->email, $payload);
        }

        return redirect()->route('admin.dentists.show', $dentist)->with('ok', 'Odontólogo creado.');
    }

    /** Perfil */
    public function show(Dentist $dentist)
    {
        $dentist->load(['chair:id,name', 'user:id,name,email,phone']);

        $upcoming = Appointment::with(['patient:id,first_name,last_name,phone', 'service:id,name'])
            ->where('dentist_id', $dentist->id)
            ->whereDate('date', '>=', Carbon::today()->toDateString())
            ->orderByDesc('date')        // más recientes primero
            ->orderByDesc('start_time')  // y dentro del día, la más cercana al final primero
            ->paginate(10)               // paginar 10 por página
            ->withQueryString();         // preserva query string al paginar

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
            'ci'        => ['required', 'string', 'unique:dentists,ci,' . $dentist->id],
            'address'   => ['nullable', 'string', 'max:255'],
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
                'ci'        => $base['ci'],
                'address'   => $base['address'] ?? null,
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


    /** Toggle Active Status (Logical Delete) */
    public function toggle(Dentist $dentist)
    {
        // 1 active, 0 inactive
        $newState = $dentist->status == 1 ? 0 : 1;
        
        $dentist->update(['status' => $newState]);

        // Sincronizar usuario si existe
        if ($dentist->user) {
            // Si el dentista se desactiva, suspendemos usuario.
            // Si se activa, activamos usuario.
            $dentist->user->update([
                'status' => $newState ? 'active' : 'suspended'
            ]);

            // --- EMAIL: Account Suspended / Reactivated ---
            if ($dentist->user->wasChanged('status')) {
                 $user = $dentist->user;
                 if ($user->status === 'suspended') {
                    try {
                        \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\AccountSuspended($user));
                        \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Aviso de Suspensión de Cuenta - DentalCare',
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                         \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Aviso de Suspensión de Cuenta - DentalCare',
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                        ]);
                    }
                 } elseif ($user->status === 'active') {
                    try {
                         \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\AccountReactivated($user));
                        \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Tu Cuenta ha sido Reactivada - DentalCare',
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                         \App\Models\EmailLog::create([
                            'to' => $user->email,
                            'subject' => 'Tu Cuenta ha sido Reactivada - DentalCare',
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                        ]);
                    }
                 }
            }
        }

        $verb = $newState ? 'activado' : 'desactivado';
        return back()->with('ok', "Odontólogo $verb correctamente.");
    }

    /** Eliminar -> ahora llama a toggle o impide borrado físico */
    public function destroy(Dentist $dentist)
    {
        return $this->toggle($dentist);
    }
}
