<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; color: #374151; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #064e3b; margin: 0; }
        .creds { background: #ecfdf5; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px dashed #10b981; }
        .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido a DentalCare</h1>
        </div>
        
        <p>Hola <strong>{{ $user->name }}</strong>,</p>
        <p>Tu cuenta ha sido creada exitosamente en nuestro sistema.</p>
        
        @if($password)
        <div class="creds">
            <p>Tus credenciales de acceso son:</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Contraseña Temporal:</strong> {{ $password }}</p>
            <p><em>Te recomendamos cambiar tu contraseña al ingresar.</em></p>
        </div>
        @endif

        <p>Ahora puedes acceder al panel administrativo para gestionar tus tareas según tu rol.</p>
        
        <div class="footer">
            Sistema de Gestión DentalCare
        </div>
    </div>
</body>
</html>
