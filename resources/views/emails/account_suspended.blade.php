<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fef2f2; color: #374151; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-top: 4px solid #ef4444; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #b91c1c; margin: 0; }
        .alert { background: #fee2e2; padding: 15px; border-radius: 6px; margin: 20px 0; color: #991b1b; }
        .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Aviso de Cuenta</h1>
        </div>
        
        <p>Hola <strong>{{ $user->name }}</strong>,</p>
        
        <div class="alert">
            <p><strong>Tu cuenta de acceso a DentalCare ha sido SUSPENDIDA.</strong></p>
        </div>

        <p>A partir de este momento, no podrás iniciar sesión en el sistema.</p>
        <p>Si crees que esto es un error o necesitas más información, por favor contacta al administrador del sistema.</p>
        
        <div class="footer">
            Departamento de Administración - DentalCare
        </div>
    </div>
</body>
</html>
