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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => $response];
}

echo "Testing Diagnosis for 'Dolor de muela'...\n";
$res = callApi('POST', '/diagnosis', ['text' => 'Me duele mucho la muela y tengo sensibilidad']);
echo "Code: {$res['code']}\n";
echo "Response: {$res['response']}\n";
