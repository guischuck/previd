<?php
$response = file_get_contents('http://localhost:8000/advbox_api.php?endpoint=settings');
$data = json_decode($response, true);

echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "Users count: " . count($data['users'] ?? []) . "\n";
echo "Tasks count: " . count($data['tasks'] ?? []) . "\n";
echo "Errors: " . json_encode($data['errors'] ?? []) . "\n";

if (!empty($data['users'])) {
    echo "First user: " . json_encode($data['users'][0]) . "\n";
}

if (!empty($data['tasks'])) {
    echo "First task: " . json_encode($data['tasks'][0]) . "\n";
}
?>