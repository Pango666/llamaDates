<?php

$hosts = [
    'smtp-relay-offshore-southamerica-east-v2.sendinblue.com',
    'smtp-relay.sendinblue.com',
    'smtp-relay.brevo.com'
];

$port = 587;
$timeout = 10;

echo "Probando conectividad SMTP...\n";

foreach ($hosts as $host) {
    echo "\n--------------------------------------------------\n";
    echo "Intentando conectar a: $host:$port\n";
    
    $startTime = microtime(true);
    $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, $timeout);
    $endTime = microtime(true);
    
    if ($fp) {
        echo "✅ ÉXITO: Conexión establecida en " . round($endTime - $startTime, 4) . " segundos.\n";
        fwrite($fp, "EHLO dentalcare.local\r\n");
        $response = fread($fp, 1024);
        echo "Respuesta del servidor: " . trim($response) . "\n";
        fclose($fp);
    } else {
        echo "❌ ERROR: No se pudo conectar.\n";
        echo "Código de error: $errno\n";
        echo "Mensaje de error: $errstr\n";
    }
}
echo "\n--------------------------------------------------\n";
