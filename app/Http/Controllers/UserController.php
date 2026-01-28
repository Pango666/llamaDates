<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $q      = trim((string) $r->get('q', ''));
        $status = $r->get('status'); // 'active' | 'suspended' | null
        $role   = $r->get('role');   // 'admin', 'asistente', ...

        $users = User::query()
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($role,   fn($qq) => $qq->where('role', $role))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        $totals = [
            'total'     => User::count(),
            'active'    => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'roles'     => User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role'),
        ];

        return view('admin.users.index', compact('users', 'q', 'totals'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'perms'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'role'  => ['required', Rule::in(['admin', 'asistente', 'odontologo', 'paciente', 'cajero', 'almacen', 'enfermera'])],
            'roles' => ['array'],              // ids de la tabla roles
            'roles.*' => ['integer', 'exists:roles,id'],
            'perms' => ['array'],
            'perms.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'role' => $data['role'], // sincroniza enum “rol principal”
        ]);

        // Auto-assign Laratrust Role based on main role selection
        // We assume Role names match the enum values (e.g. 'admin', 'odontologo')
        $roleObj = Role::where('name', $data['role'])->first();
        if ($roleObj) {
            $user->roles()->sync([$roleObj->id]);
        } else {
            // Fallback: if no matching role found in DB, just clear roles or log warning.
            // For now, we sync empty or keep manual input if we wanted to support both (but we are hiding manual).
            // We'll stick to auto-sync.
            $user->roles()->detach();
        }
        
        // Manual permissions still allowed if passed
        $user->permissions()->sync($data['perms'] ?? []);

        // --- EMAIL: Welcome ---
        try {
            // Enviamos el password solo si se creó ahora (que es el caso)
            \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\WelcomeUser($user, $data['password']));
            
            \App\Models\EmailLog::create([
                'to' => $user->email,
                'subject' => 'Bienvenido a DentalCare',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
             \App\Models\EmailLog::create([
                'to' => $user->email,
                'subject' => 'Bienvenido a DentalCare',
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.users.index')->with('ok', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();
        $user->load(['roles', 'permissions']);
        return view('admin.users.edit', compact('user', 'roles', 'perms'));
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'role'  => ['required', Rule::in(['admin', 'asistente', 'odontologo', 'paciente', 'cajero', 'almacen', 'enfermera'])],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'perms' => ['array'],
            'perms.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'role' => $data['role'],
            ...(isset($data['password']) && $data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        // Auto-assign Laratrust Role based on main role selection
        $roleObj = Role::where('name', $data['role'])->first();
        if ($roleObj) {
            $user->roles()->sync([$roleObj->id]);
        } else {
             $user->roles()->detach();
        }

        $user->permissions()->sync($data['perms'] ?? []);

        // --- EMAIL: Account Suspended / Reactivated ---
        if ($user->wasChanged('status')) {
            // Caso: Suspensión
            if ($data['status'] === 'suspended') {
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
            }
            // Caso: Reactivación
            elseif ($data['status'] === 'active') {
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

        return redirect()->route('admin.users.index')->with('ok', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('ok', 'Usuario eliminado.');
    }
}
