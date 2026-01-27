<?php

$baseUrl = 'http://127.0.0.1:8081/api/bot';

function callApi($method, $endpoint, $data = []) {
    global $baseUrl;
    $url = $baseUrl . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'response' => $response];
}

echo "Testing Bot API...\n";

// ... (keep helper function)

echo "Testing Bot API Full Flow...\n";

// 1. Get Service ID
echo "\n[GET] /services\n";
$res = callApi('GET', '/services');
$services = json_decode($res['response'], true);
$serviceId = $services[0]['id'] ?? 1;
echo "Service ID: $serviceId\n";

// 2. Get Dentist ID
echo "\n[GET] /dentists\n";
$res = callApi('GET', '/dentists');
$dentists = json_decode($res['response'], true);
$dentistId = $dentists[0]['id'] ?? 1;
echo "Dentist ID: $dentistId\n";

// 3. Register Patient
$ci = rand(100000, 999999);
echo "\n[POST] /register (CI: $ci)\n";
$res = callApi('POST', '/register', [
    'first_name' => 'BotUser',
    'last_name'  => 'Test',
    'ci'         => (string)$ci,
    'phone'      => '555' . $ci,
    'email'      => "bot_$ci@test.com"
]);
echo "Code: {$res['code']}\n";
$regData = json_decode($res['response'], true);
$patientId = $regData['patient_id'] ?? null;
echo "Patient ID: " . ($patientId ?? 'NULL') . "\n";

// 4. Book Appointment
if ($patientId) {
    echo "\n[POST] /book\n";
    $date = date('Y-m-d', strtotime('+1 day'));
    $time = '10:00'; // Hope it's free, or we might get 409
    
    $res = callApi('POST', '/book', [
        'patient_id' => $patientId,
        'dentist_id' => $dentistId,
        'service_id' => $serviceId,
        'date'       => $date,
        'start_time' => $time,
        'notes'      => 'Test booking from bot script'
    ]);
    echo "Code: {$res['code']}\n";
    echo "Response: {$res['response']}\n";
} else {
    echo "Skipping booking test due to registration failure.\n";
}

// 5. Test Diagnosis Logic
echo "\n[POST] /diagnosis (Text: Dolor de muela)\n";
$res = callApi('POST', '/diagnosis', ['text' => 'Dolor de muela']);
echo "Code: {$res['code']}\n";
echo "Response: {$res['response']}\n";

// 6. Test Patient Not Found (Soft Error)
echo "\n[POST] /check-patient (Invalid CI)\n";
$res = callApi('POST', '/check-patient', ['identifier' => '00000000']);
echo "Code: {$res['code']}\n";
echo "Response: {$res['response']}\n";

// 7. Test Complex Diagnosis (User Reported Case)
$complexUi = "Dolor de dientes, aumento de sensibilidad, aparición de llagas por roce, dificultad para masticar y acumulación de sarro";
echo "\n[POST] /diagnosis (Complex Text)\n";
$res = callApi('POST', '/diagnosis', ['text' => $complexUi]);
echo "Code: {$res['code']}\n";
echo "Response: {$res['response']}\n";

