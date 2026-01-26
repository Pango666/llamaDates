<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; color: #374151; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #1e3a8a; margin: 0; }
        .details { background: #eff6ff; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #3b82f6; }
        .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DentalCare</h1>
            <p>Confirmación de Cita</p>
        </div>
        
        <p>Hola <strong>{{ $appointment->patient->name }}</strong>,</p>
        <p>Tu cita ha sido agendada exitosamente. Aquí están los detalles:</p>
        
        <div class="details">
            <p><strong>Fecha:</strong> {{ $appointment->date->format('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</p>
            <p><strong>Doctor:</strong> {{ $appointment->dentist->name }}</p>
            <p><strong>Motivo:</strong> {{ $appointment->service->name }}</p>
            @if($appointment->notes)
            <p><strong>Notas:</strong> {{ $appointment->notes }}</p>
            @endif
        </div>

        <p>Si necesitas reprogramar, por favor contáctanos con anticipación.</p>
        
        <p>¡Te esperamos!</p>

        <div class="footer">
            Este es un correo automático, por favor no respondas a este mensaje.
        </div>
    </div>
</body>
</html>
