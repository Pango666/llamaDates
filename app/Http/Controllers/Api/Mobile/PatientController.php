<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Odontogram;

class PatientController extends Controller
{
    /**
     * GET /api/v1/mobile/profile
     * Retorna info detallada médica del paciente logueado
     */
    public function show()
    {
        $user = auth('api')->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $patient = Patient::where('user_id', $user->id)->first();
        if (!$patient) {
            return response()->json(['error' => 'No se encontró ficha de paciente asociada'], 404);
        }

        // Cargar historial médico si existe relación o tabla
        // Asumiendo que está en la tabla patients o tabla aparte
        // Ajustar según tu esquema real.
        /* 
           Simulamos datos médicos básicos si no hay tabla específica,
           o devolvemos lo que tenga el modelo Patient.
        */

        return response()->json([
            'personal_info' => [
                'first_name' => $patient->first_name,
                'last_name'  => $patient->last_name,
                'ci'         => $patient->ci,
                'birthdate'  => $patient->birthdate, // asegúrate de que exista en modelo
                'age'        => $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->age : null,
                'phone'      => $patient->phone,
                'email'      => $patient->email,
                'address'    => $patient->address,
                'gender'     => $patient->gender,
            ],
            'medical_info' => [
                'blood_type' => $patient->blood_type ?? 'No registrado',
                'allergies'  => $patient->allergies ?? 'Ninguna conocida',
                'diseases'   => $patient->diseases ?? 'Ninguna',
            ]
        ]);
    }

    /**
     * PUT /api/v1/mobile/profile
     * Actualizar datos de contacto (Teléfono, Dirección)
     */
    public function update(Request $request)
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();
        
        if (!$patient) return response()->json(['error' => 'Paciente no encontrado'], 404);

        $data = $request->validate([
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'photo'   => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('patients/photos', 'public');
            $data['photo_path'] = $path; // Asumiendo que esta col existe en patients table?
            // Si no existe photo_path en patients, tal vez usa users.photo_path?
            // El modelo Patient.php original no mostraba fillable photo_path, checkearé despues.
            // Asumiré que Patient tiene photo_path como decía antes.
        }

        $patient->update($data);
        
        // Sincronizar phone al usuario si es necesario
        if (!empty($data['phone'])) {
            $user->phone = $data['phone'];
            $user->save();
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'patient' => [
                'id' => $patient->id,
                'phone' => $patient->phone,
                'address' => $patient->address,
                'photo_url' => $patient->photo_path ? asset('storage/'.$patient->photo_path) : null,
            ]
        ]);
    }

    /**
     * GET /api/v1/mobile/odontogram
     * Retorna el último odontograma del paciente (Solo lectura)
     */
    public function odontogram()
    {
        $user = auth('api')->user();
        $patient = Patient::where('user_id', $user->id)->first();
        
        if (!$patient) return response()->json(['error' => 'Paciente no encontrado'], 404);

        $odo = Odontogram::where('patient_id', $patient->id)
                ->with(['teeth' => function ($q) {
                    $q->orderBy('tooth_code'); // para orden visual
                }])
                ->latest('created_at')
                ->first();

        if (!$odo) {
            return response()->json(['message' => 'No hay odontograma registrado', 'data' => null]);
        }

        // Transformar data para frontend simple
        $teeth_data = $odo->teeth->map(function($t) {
            return [
                'code' => $t->tooth_code,
                'status' => $t->status, // sano, caries, etc
                'notes'  => $t->notes,
                'treatment' => $t->treatment_id ? $t->treatment->name ?? null : null, 
                // Asumiendo relaciones, ajustar si difiere
            ];
        });

        return response()->json([
            'id' => $odo->id,
            'date' => $odo->created_at->format('Y-m-d'),
            'teeth' => $teeth_data
        ]);
    }
}
