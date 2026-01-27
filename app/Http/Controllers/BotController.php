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
use App\Mail\AppointmentConfirmation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

        $patient = Patient::where('ci', $val)
            ->orWhere('phone', $val)
            ->first();

        if (!$patient) {
            // Retornamos 200 con exists:false para que el bot no lo tome como error de sistema
            return response()->json([
                'exists' => false,
                'message' => 'No encontramos un paciente registrado con ese dato. ¿Deseas registrarte?'
            ], 200);
        }

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

    // ... (registerPatient, getServices, getDentists, getSlots, bookAppointment remain same) ...

    /**
     * Mis Citas (Futuras)
     */
    public function myAppointments(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', 
        ]);

        $patient = Patient::where('ci', $request->identifier)
             ->orWhere('phone', $request->identifier)
             ->first();

        if (!$patient) {
             // 200 OK con mensaje amigable
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
     * Diagnóstico Simulado (Keyword Matching con Scoring)
     */
    public function aiDiagnosis(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:3',
        ]);

        $text = strtolower($request->text);
        
        // Mapa de palabras clave -> ID o Patrón de búsqueda
        // 'clave_bd' => ['keyword1', 'keyword2', ...]
        $rules = [
            'endodoncia' => ['dolor intenso', 'nervio', 'palpita', 'frio', 'calor', 'matar nervio', 'conducto', 'pulpa', 'abscesso', 'sensibilidad'],
            'ortodoncia' => ['brackets', 'chuecos', 'alinear', 'frenillos', 'morder', 'ordenar', 'separados', 'apiñados', 'roce', 'llaga', 'alambre'],
            'limpieza'   => ['sarro', 'limpieza', 'higiene', 'sucio', 'manchas', 'calculo', 'tártaro', 'mal aliento'],
            'blanqueamiento' => ['blanquear', 'amarillos', 'estetica', 'brillantes', 'aclarar'],
            'implante'   => ['falta', 'diente', 'perdi', 'hueco', 'implante', 'tornillo', 'ausencia'],
            'extraccion' => ['sacar', 'extraer', 'rota', 'juicio', 'muela del juicio', 'cirugia'],
            'consulta'   => ['dolor', 'molestia', 'revision', 'consulta', 'chequeo', 'duda', 'general', 'evaluacion'],
            // 'dolor' simple ahora apunta a consulta general si no hay keywords más específicas
        ];

        $scores = [];
        // Inicializar scores
        foreach ($rules as $key => $kws) {
            $scores[$key] = 0;
        }

        // Calcular puntaje
        foreach ($rules as $serviceKey => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    // Dar más peso a palabras compuestas/específicas si se desea, 
                    // por ahora peso simple = 1
                    $scores[$serviceKey]++;
                }
            }
        }

        // Ordenar scores descendente
        arsort($scores);
        
        // Obtener el ganador
        $bestKey = array_key_first($scores);
        $bestScore = $scores[$bestKey];

        $suggestedService = null;
        $reason = "";

        // Solo sugerimos si hay al menos una coincidencia
        if ($bestScore > 0) {
            // Buscar servicio en DB
            $dbService = Service::where('name', 'like', "%{$bestKey}%")->where('active', true)->first();
            
            if ($dbService) {
                $suggestedService = $dbService;
                $reason = "Basado en tus síntomas (palabras clave detectadas), te sugerimos: " . ucfirst($bestKey);
            } else {
                // Si ganó una categoría pero no existe el servicio con ese nombre exacto en BD, buscar fallback
                // E.g. ganó 'consulta' -> busca 'Consulta' o 'General'
                 if ($bestKey === 'consulta') {
                    $suggestedService = Service::where('name', 'like', '%Consulta%')->where('active', true)->first();
                    $reason = "Para una evaluación completa de tus síntomas, te recomendamos una Consulta General.";
                 }
            }
        }

        // Fallback final: Si el score es 0 o no se encontró el servicio en BD
        if (!$suggestedService) {
            // Intentar buscar 'Consulta'
            $suggestedService = Service::where('name', 'like', '%Consulta%')->where('active', true)->first();
            $reason = "No pudimos identificar un tratamiento específico para tu descripción. Lo mejor es una evaluación general.";
            
            if (!$suggestedService) {
                $suggestedService = Service::first(); // Ultimate fallback
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
