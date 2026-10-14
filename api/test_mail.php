<?php
// Diagnostics script to test mail sending and database connectivity on cPanel
header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'mail_function_enabled' => function_exists('mail'),
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'unknown'
];

// Test DB
require_once __DIR__ . '/db.php';
try {
    $pdo = getDBConnection();
    $results['database_connection'] = 'SUCCESS';
} catch (\Exception $e) {
    $results['database_connection'] = 'FAILED: ' . $e->getMessage();
}

// Test Mail to Dr. Wangari
$to = 'careywangaretracy@gmail.com';
$from = 'dr.vet@pawhooves.co.ke';
$subject = "Paws & Hooves Diagnostic Test - " . date('H:i:s');
$message = "This is a direct diagnostic test from https://" . ($results['http_host'] ?? 'pawhooves.co.ke') . " to verify mail delivery to Dr. Wangari.";

$headers = [
    'From: Paws & Hooves Test <' . $from . '>',
    'Reply-To: ' . $from,
    'X-Mailer: PHP/' . phpversion()
];

// Use -f envelope parameter for SPF/DKIM compliance
$additional_params = "-f" . $from;

$sent_with_param = @mail($to, $subject, $message, implode("\r\n", $headers), $additional_params);
$results['mail_sent_with_envelope_from'] = $sent_with_param;

if (!$sent_with_param) {
    $sent_standard = @mail($to, $subject, $message, implode("\r\n", $headers));
    $results['mail_sent_standard'] = $sent_standard;
}

echo json_encode($results, JSON_PRETTY_PRINT);
