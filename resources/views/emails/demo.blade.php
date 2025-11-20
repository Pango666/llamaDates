<!doctype html>
<html>
  <body style="font-family: Arial, Helvetica, sans-serif;">
    <h2>Hola {{ $nombre }}</h2>
    <p>Este es un correo de prueba de <strong>LlamaDates</strong>.</p>

    <p style="margin-top:16px;">
      Puedes incluir imágenes hosteadas:<br>
      <img src="https://tu-dominio.com/assets/logo.png" alt="LlamaDates" width="160">
    </p>

    <hr>
    <small>© {{ date('Y') }} LlamaDates</small>
  </body>
</html>
