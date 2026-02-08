<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

session_start();
include("../connect.php");

header('Content-Type: application/json');

$response = [
    'session' => isset($_SESSION['email']) ? 'Logged in as: ' . $_SESSION['email'] : 'Not logged in',
    'database' => $conn ? 'Connected' : 'Not connected',
    'table_check' => 'Checking...'
];

if ($conn) {
    $result = $conn->query("SHOW TABLES LIKE 'compositions'");
    $response['table_check'] = $result->num_rows > 0 ? 'Table exists' : 'Table missing';
    
    if ($response['table_check'] === 'Table exists') {
        $structure = $conn->query("DESCRIBE compositions");
        $columns = [];
        while ($row = $structure->fetch_assoc()) {
            $columns[] = $row['Field'] . ' (' . $row['Type'] . ')';
        }
        $response['columns'] = $columns;
    }
}

ob_end_clean();
echo json_encode($response);
?>