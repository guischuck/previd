<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing AdvBox API Settings Endpoint\n";
echo "=====================================\n";

$url = 'http://localhost:8000/advbox_api.php?endpoint=settings';
echo "URL: $url\n";

$response = file_get_contents($url);
echo "Raw Response: " . $response . "\n";
echo "Response Length: " . strlen($response) . "\n";

if ($response === false) {
    echo "ERROR: Failed to get response\n";
    exit;
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    exit;
}

echo "Decoded Data: " . print_r($data, true) . "\n";
?>