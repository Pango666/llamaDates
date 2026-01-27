<?php

$baseUrl = 'http://127.0.0.1:8081/api/bot';

function callApi($method, $endpoint, $data = []) {
    global $baseUrl;
    $url = $baseUrl . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => $response];
}

echo "\n--- Registering Jose Mollinedo (Role Check) ---\n";
$data = [
    'first_name' => 'Jose',
    'last_name' => 'Mollinedo',
    'ci' => '13788818',
    'email' => 'joseckan1@gmail.com',
    'phone' => '59170000000', 
];
$res = callApi('POST', '/register', $data);
echo "Code: {$res['code']}\n";
echo "Response: {$res['response']}\n";
