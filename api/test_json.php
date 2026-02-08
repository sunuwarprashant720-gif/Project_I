<?php
// Turn off ALL output
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');

// Simple test response
echo json_encode([
    'success' => true,
    'message' => 'Test successful',
    'time' => date('Y-m-d H:i:s')
]);

// Clean output buffer and exit
ob_end_flush();
exit();
?>