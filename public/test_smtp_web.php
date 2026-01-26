<?php

$hosts = [
    'smtp-relay-offshore-southamerica-east-v2.sendinblue.com',
    'smtp-relay.sendinblue.com',
    'smtp-relay.brevo.com'
];

$ports = [587, 2525];
$timeout = 5;

echo "<pre>";
echo "Testing SMTP Connectivity from Web Server User: " . get_current_user() . "\n";

foreach ($hosts as $host) {
    foreach ($ports as $port) {
        echo "\n--------------------------------------------------\n";
        echo "Trying to connect to: $host:$port\n";
        
        $startTime = microtime(true);
        $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, $timeout);
        $endTime = microtime(true);
        
        if ($fp) {
            echo "✅ SUCCESS: Connected in " . round($endTime - $startTime, 4) . "s.\n";
            fwrite($fp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            $response = fread($fp, 1024);
            echo "Server Response: " . trim($response) . "\n";
            fclose($fp);
        } else {
            echo "❌ ERROR: Could not connect.\n";
            echo "Error: $errno - $errstr\n";
        }
    }
}
echo "\n--------------------------------------------------\n";
echo "</pre>";
