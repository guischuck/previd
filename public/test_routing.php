<?php
header('Content-Type: application/json');

echo "Testing routing...\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Endpoint: " . ($_GET['endpoint'] ?? 'none') . "\n";

if ($_GET['endpoint'] === 'settings') {
    echo json_encode([
        'success' => true,
        'users' => [['id' => 1, 'name' => 'Test User']],
        'tasks' => [['id' => 1, 'name' => 'Test Task']],
        'errors' => []
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Unknown endpoint'
    ]);
}
?>