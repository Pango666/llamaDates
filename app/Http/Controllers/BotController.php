<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\User;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\EmailLog;
use App\Models\Role;
use App\Mail\AppointmentConfirmation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    /**
     * Verificar si el paciente existe por CI o Celular
     */
    public function checkPatient(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', 
        ]);

        $val = $request->identifier;
        Log::info("[Bot] CheckPatient: Buscando '$val'");

        $patient = Patient::where('ci', $val)
            ->orWhere('phone', $val)
            ->first();

        if (!$patient) {
            Log::info("[Bot] CheckPatient: No encontrado '$val'");
            // Retornamos 200 con exists:false para que el bot no lo tome como error de sistema
            return response()->json([
                'exists' => false,
                'message' => 'No encontramos un paciente registrado con ese dato. ¿Deseas registrarte?'
            ], 200);
        }

        Log::info("[Bot] CheckPatient: Encontrado PatientID: {$patient->id}");

        return response()->json([
            'exists' => true,
            'patient' => [
                'id' => $patient->id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'ci' => $patient->ci,
            ]
        ]);
    }

    /**
     * Registrar nuevo paciente
     */
    public function registerPatient(Request $request)
    {
        Log::info("[Bot] RegisterPatient: Intento de registro", $request->all());

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'ci' => 'required|string|unique:patients,ci',
            'email' => 'nullable|email|unique:users,email', // Email unico en usuarios
            'phone' => 'required|string',
        ]);

        // Limpiar prefijo 591 si viene del bot
        $phone = $request->phone;
        if (str_starts_with($phone, '591')) {
            $phone = substr($phone, 3);
        }

        // 1. Crear Paciente
        $patient = Patient::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'ci' => $request->ci,
            'phone' => $phone,
            'email' => $request->email,
            'address' => $request->address ?? 'Sin dirección',
            'birth_date' => $request->birth_date ?? null,
            'gender' => $request->gender ?? 'varios',
        ]);

        Log::info("[Bot] RegisterPatient: Paciente creado ID: {$patient->id}");

        // 2. Crear Usuario de Portal (Opcional, pero recomendado)
        // Usamos CI como password por defecto
        if ($request->email) {
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->ci),
                'status' => 1, // Active
                'role' => 'paciente', // Explicitly set role column
            ]);
            
            // Asignar rol manualmente (si User no usa HasRoles trait)
            $role = Role::where('name', 'paciente')->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }
            
            // Vincular
            $patient->user_id = $user->id;
            $patient->save();
            Log::info("[Bot] RegisterPatient: Usuario creado y vinculado ID: {$user->id}");

            // Enviar email bienvenida (si existe la clase Mailable)
            // Mail::to($user)->send(new \App\Mail\WelcomeUser($user));
        }

        return response()->json([
            'message' => 'Paciente registrado exitosamente.',
            'patient_id' => $patient->id
        ], 201);
    }

    public function getServices()
    {
        return Service::where('active', true)->select('id', 'name', 'price')->get();
    }



    public function getDentists()
    {
        // Query Dentist model correctly
        $dentists = Dentist::where('status', 1)
            ->select('id', 'name')
            ->get();

        if ($dentists->isEmpty()) {
             // Fallback logic could serve random active dentists, 
             // but strictly we should return empty if none found.
             // For safety/demo, we might check Users if Dentists table empty? 
             // No, strictly use Dentists.
        }
        
        return $dentists;
    }

    /**
     * Obtener horarios disponibles
     */
    public function getSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
        ]);

        Log::info("[Bot] GetSlots: Solicitud fecha={$request->date} doc={$request->dentist_id} srv={$request->service_id}");

        $date = $request->date;
        $dentistId = $request->dentist_id;
        $serviceId = $request->service_id;
        
        $service = Service::find($serviceId);
        $duration = $service->duration_min ?? 30;

        $dayOfWeek = Carbon::parse($date)->dayOfWeek; 
        
        // 1. Get ALL shifts (morning, afternoon)
        $schedules = Schedule::where('dentist_id', $dentistId)
                            ->where('day_of_week', $dayOfWeek) 
                            ->get();

        if ($schedules->isEmpty()) {
             Log::info("[Bot] GetSlots: No hay horarios configurados para ese día.");
             return response()->json(['slots' => []]);
        }

        // 2. Fetched Existing Appointments
        $appointments = Appointment::where('dentist_id', $dentistId)
                                   ->where('date', $date)
                                   ->whereIn('status', ['confirmed', 'reserved'])
                                   ->where('is_active', true)
                                   ->get();

        $slots = [];
        $now = now();
        $isToday = ($date === $now->toDateString());

        foreach ($schedules as $schedule) {
            $startWork = Carbon::parse($date . ' ' . $schedule->start_time);
            $endWork = Carbon::parse($date . ' ' . $schedule->end_time);
            
            // 3. Parse Breaks
            $breaks = [];
            if (!empty($schedule->breaks)) {
                $rawBreaks = is_string($schedule->breaks) ? json_decode($schedule->breaks, true) : $schedule->breaks;
                foreach ($rawBreaks ?? [] as $br) {
                    $breaks[] = [
                        'start' => Carbon::parse($date . ' ' . $br['start']),
                        'end'   => Carbon::parse($date . ' ' . $br['end'])
                    ];
                }
            }

            $current = $startWork->copy();

            while ($current->copy()->addMinutes($duration)->lte($endWork)) {
                $slotStart = $current->copy();
                $slotEnd   = $current->copy()->addMinutes($duration);

                // 4. Skip Past
                if ($isToday && $slotStart->lt($now)) {
                    $current->addMinutes($duration);
                    continue;
                }

                $isFree = true;

                // 5. Check Appointment Overlap
                foreach ($appointments as $appt) {
                    $apptStart = Carbon::parse($date . ' ' . $appt->start_time);
                    $apptEnd   = Carbon::parse($date . ' ' . $appt->end_time);

                    if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                        $isFree = false;
                        break;
                    }
                }

                // 6. Check Breaks Overlap
                if ($isFree) {
                    foreach ($breaks as $br) {
                        if ($slotStart->lt($br['end']) && $slotEnd->gt($br['start'])) {
                            $isFree = false;
                            break;
                        }
                    }
                }

                if ($isFree) {
                    $slots[] = $slotStart->format('H:i');
                }

                $current->addMinutes($duration);
            }
        }

        $slots = array_values(array_unique($slots));
        sort($slots);

        Log::info("[Bot] GetSlots: " . count($slots) . " horarios encontrados.");

        return response()->json(['slots' => $slots]);
    }

    /**
     * Reservar Cita
     */
    public function bookAppointment(Request $request)
    {
        Log::info("[Bot] BookAppointment: Inicio", $request->all());

        $request->validate([
            'patient_id' => 'required_without:patient_identifier',
            'patient_identifier' => 'nullable|string', 
            'dentist_id' => 'required|exists:dentists,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $patient = null;
        if ($request->patient_id) {
            $patient = Patient::find($request->patient_id);
        } elseif ($request->patient_identifier) {
            $patient = Patient::where('ci', $request->patient_identifier)
                            ->orWhere('phone', $request->patient_identifier)
                            ->first();
        }

        if (!$patient) {
            Log::warning("[Bot] BookAppointment: Paciente no encontrado");
            return response()->json(['error' => 'Paciente no encontrado.'], 404);
        }

        $service = Service::find($request->service_id);
        $endTime = Carbon::parse($request->time)->addMinutes($service->duration_min ?? 30)->format('H:i:s');

        $dentist = Dentist::find($request->dentist_id); // Fetch full model
        
        try {
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'dentist_id' => $request->dentist_id,
                'chair_id'   => $dentist->chair_id, // Assign chair from dentist
                'service_id' => $request->service_id,
                'date' => $request->date,
                'start_time' => $request->time,
                'end_time' => $endTime,
                'status' => 'confirmed',
                'notes' => 'Reservado vía WhatsApp Bot',
            ]);

            Log::info("[Bot] BookAppointment: Cita creada ID: {$appointment->id}");

            try {
                if ($patient->email) {
                    Mail::to($patient->email)->send(new AppointmentConfirmation($appointment));
                    Log::info("[Bot] BookAppointment: Email enviado a {$patient->email}");
                }
            } catch (\Exception $e) {
                Log::error("Email error: " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Cita reservada con éxito.',
                'appointment_id' => $appointment->id
            ], 201);

        } catch (\Exception $e) {
            Log::error("[Bot] BookAppointment Error: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al guardar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mis Citas (Futuras)
     */
    public function myAppointments(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', 
        ]);

        Log::info("[Bot] MyAppointments: Buscando para '{$request->identifier}'");

        $patient = Patient::where('ci', $request->identifier)
             ->orWhere('phone', $request->identifier)
             ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'No encontramos un paciente con ese documento. Verifícalo o regístrate en el menú principal.',
                'appointments' => []
            ], 200); 
        }

        $appointments = Appointment::with(['dentist:id,name', 'service:id,name'])
            ->where('patient_id', $patient->id)
            ->where('date', '>=', now()->toDateString())
            ->whereIn('status', ['reserved', 'confirmed'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'date' => $a->date, 
                'time' => substr($a->start_time, 0, 5),
                'dentist' => $a->dentist->name ?? 'N/A',
                'service' => $a->service->name ?? 'N/A',
                'status' => $a->status,
            ]);
        
        Log::info("[Bot] MyAppointments: " . count($appointments) . " citas encontradas para PatientID {$patient->id}");

        if ($appointments->isEmpty()) {
             return response()->json([
                'message' => 'Hola ' . $patient->first_name . ', no tienes citas futuras programadas.',
                'appointments' => []
            ]);
        }

        return response()->json([
            'message' => 'Hola ' . $patient->first_name . ', aquí están tus próximas citas:',
            'appointments' => $appointments
        ]);
    }

    /**
     * Diagnóstico IA (Real con Gemini API)
     */
    public function aiDiagnosis(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:3',
        ]);

        Log::info("[Bot] AI Diagnosis: Texto recibido '{$request->text}'");

        $text = $request->text;
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            Log::warning("[Bot] AI Diagnosis: No API Key found, using basic fallback.");
            return $this->basicDiagnosis($text);
        }

        try {
            $services = Service::where('active', true)
                ->select('id', 'name', 'price')
                ->get()
                ->toArray();

            $servicesJson = json_encode($services);

            $prompt = <<<EOT
Actúa como un asistente dental experto.
Tengo la siguiente lista de servicios disponibles:
$servicesJson

El paciente reporta el siguiente síntoma o necesidad: "$text".

Tu tarea:
1. Analiza el síntoma.
2. Encuentra el servicio MÁS adecuado de la lista.
3. Responde ÚNICAMENTE con un objeto JSON (sin markdown, sin texto extra) con este formato:
{
  "service_id": ID_DEL_SERVICIO,
  "reason": "Breve explicación de por qué sugieres este servicio (máximo 1 frase amigable)."
}

Si el síntoma no coincide claramente con ninguno, o es muy ambiguo, usa el servicio de "Consulta General" (o el que tenga 'Consulta' en el nombre) y explica por qué.
Si no puedes determinar nada, responde null.
EOT;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $aiData = json_decode($content, true);

                if ($aiData && isset($aiData['service_id'])) {
                    $service = Service::find($aiData['service_id']);
                    if ($service) {
                        Log::info("[Bot] AI Diagnosis: Sugerido '{$service->name}' Reason: " . ($aiData['reason'] ?? ''));
                        return response()->json([
                            'message' => $aiData['reason'] ?? "Te recomendamos este servicio basado en tus síntomas.",
                            'suggested_services' => [
                                [
                                    'id' => $service->id,
                                    'name' => $service->name,
                                    'price' => $service->price,
                                ]
                            ]
                        ]);
                    }
                }
            } else {
                Log::error("Gemini API Error: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("AI Diagnosis Exception: " . $e->getMessage());
        }

        return $this->basicDiagnosis($text, "Hubo un problema conectando con la IA, pero basado en palabras clave:");
    }

    /**
     * Fallback: Diagnóstico básico por keywords (Scoring logic)
     */
    private function basicDiagnosis($text, $prefix = "")
    {
        $text = strtolower($text);
        
        $rules = [
            'endodoncia' => ['dolor intenso', 'nervio', 'palpita', 'frio', 'calor', 'matar nervio', 'conducto', 'pulpa', 'abscesso', 'sensibilidad', 'destemplamiento'],
            'ortodoncia' => ['brackets', 'chuecos', 'alinear', 'frenillos', 'morder', 'ordenar', 'separados', 'apiñados', 'roce', 'llaga', 'alambre'],
            'limpieza'   => ['sarro', 'limpieza', 'higiene', 'sucio', 'manchas', 'calculo', 'tártaro', 'mal aliento'],
            'blanqueamiento' => ['blanquear', 'amarillos', 'estetica', 'brillantes', 'aclarar'],
            'implante'   => ['falta', 'diente', 'perdi', 'hueco', 'implante', 'tornillo', 'ausencia'],
            'extraccion' => ['sacar', 'extraer', 'rota', 'juicio', 'muela del juicio', 'cirugia'],
            'consulta'   => ['dolor', 'molestia', 'revision', 'consulta', 'chequeo', 'duda', 'general', 'evaluacion'],
        ];

        $scores = [];
        foreach ($rules as $key => $kws) {
            $scores[$key] = 0;
        }

        foreach ($rules as $serviceKey => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $scores[$serviceKey]++;
                }
            }
        }

        arsort($scores);
        $bestKey = array_key_first($scores);
        $bestScore = $scores[$bestKey];

        $suggestedService = null;
        $reason = "";

        if ($bestScore > 0) {
            $dbService = Service::where('name', 'like', "%{$bestKey}%")->where('active', true)->first();
            if ($dbService) {
                $suggestedService = $dbService;
                $reason = $prefix . " Detectamos síntomas relacionados con " . ucfirst($bestKey) . ".";
            } else {
                 if ($bestKey === 'consulta') {
                    $suggestedService = Service::where('name', 'like', '%Consulta%')->where('active', true)->first();
                    $reason = $prefix . " Recomendamos una Consulta General para evaluar tus síntomas.";
                 }
            }
        }

        if (!$suggestedService) {
            $suggestedService = Service::where('name', 'like', '%Consulta%')->where('active', true)->first();
            $reason = "No pudimos identificar un tratamiento específico. Te recomendamos una Consulta General.";
            
            if (!$suggestedService) {
                $suggestedService = Service::first();
            }
        }

        return response()->json([
            'message' => $reason,
            'suggested_services' => $suggestedService ? [
                [
                    'id' => $suggestedService->id,
                    'name' => $suggestedService->name,
                    'price' => $suggestedService->price,
                ]
            ] : []
        ]);
    }

}
