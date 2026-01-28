<!DOCTYPE html>
<html>
<head>
    <title>Recordatorio de Cita</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #2563eb; text-align: center;">Recordatorio de Cita</h2>
        
        <p>Hola <strong>{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</strong>,</p>
        
        <p>Te recordamos que tienes una cita programada próximamente en <strong>DentalCare</strong>.</p>
        
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2563eb;">
            <p style="margin: 5px 0;"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}</p>
            <p style="margin: 5px 0;"><strong>Hora:</strong> {{ substr($appointment->start_time, 0, 5) }}</p>
            <p style="margin: 5px 0;"><strong>Tratamiento:</strong> {{ $appointment->service->name }}</p>
            <p style="margin: 5px 0;"><strong>Odontólogo:</strong> {{ $appointment->dentist->name }}</p>
        </div>

        <p>Por favor, intenta llegar 10 minutos antes de tu hora programada.</p>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;">
            <p>&copy; {{ date('Y') }} DentalCare. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
