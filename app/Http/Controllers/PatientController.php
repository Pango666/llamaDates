<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /** Listado + filtro simple */
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $patients = Patient::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name',  'like', "%{$q}%")
                        ->orWhere('email',      'like', "%{$q}%")
                        ->orWhere('phone',      'like', "%{$q}%")
                        ->orWhere('ci',         'like', "%{$q}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.patients.index', [
            'patients' => $patients,
            'q'        => $q,
        ]);
    }

    /** Form crear */
    public function create(Request $request)
    {
        // para el formulario vacío
        $patient = new Patient();
        return view('admin.patients.create', compact('patient'));
    }

    /** Guardar */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'ci'         => ['nullable', 'string', 'max:50'],
            'birthdate'  => ['nullable', 'date'],
            'email'      => ['nullable', 'email', 'max:150', 'unique:patients,email'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
        ]);

        $patient = Patient::create($data);

        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('ok', 'Paciente creado correctamente.');
    }

    /** Perfil */
    public function show(Patient $patient)
    {
        // últimas 10 citas del paciente
        $appointments = Appointment::with(['service:id,name', 'dentist:id,name'])
            ->where('patient_id', $patient->id)
            ->orderBy('date', 'desc')->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        // edad (si tiene fecha)
        $age = null;
        if ($patient->birthdate) {
            $age = Carbon::parse($patient->birthdate)->age;
        }

        $plans = TreatmentPlan::where('patient_id', $patient->id)
            ->withCount('treatments')
            ->orderByDesc('created_at')
            ->limit(3)->get();

        return view('admin.patients.show', compact('patient', 'age', 'appointments', 'plans'));
    }

    /** Form editar */
    public function edit(Patient $patient)
    {
        return view('admin.patients.edit', compact('patient'));
    }

    /** Actualizar */
    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'ci'         => ['nullable', 'string', 'max:50'],
            'birthdate'  => ['nullable', 'date'],
            'email'      => ['nullable', 'email', 'max:150', Rule::unique('patients', 'email')->ignore($patient->id)],
            'phone'      => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
        ]);

        $patient->update($data);

        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('ok', 'Paciente actualizado.');
    }

    /** Eliminar (soft no definido, así que será hard; respeta tus FKs en cascada) */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()
            ->route('admin.patients')
            ->with('ok', 'Paciente eliminado.');
    }
}
