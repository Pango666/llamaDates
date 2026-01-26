<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; color: #374151; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-top: 4px solid #10b981; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #064e3b; margin: 0; }
        .alert { background: #dcfce7; padding: 15px; border-radius: 6px; margin: 20px 0; color: #166534; }
        .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cuenta Activada</h1>
        </div>
        
        <p>Hola <strong>{{ $user->name }}</strong>,</p>
        
        <div class="alert">
            <p><strong>Nos complace informarte que tu cuenta de DentalCare ha sido reactivada.</strong></p>
        </div>

        <p>Ya puedes iniciar sesión nuevamente en el sistema con tus credenciales habituales.</p>
        
        <div class="footer">
            Departamento de Administración - DentalCare
        </div>
    </div>
</body>
</html>
