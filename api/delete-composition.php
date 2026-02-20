<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../connect.php");

// Check authentication
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get user ID
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT user_id AS id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_id = $user['id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'No composition ID provided']);
    exit();
}

$composition_id = intval($input['id']);

if (!$composition_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid composition ID']);
    exit();
}

// Delete composition (only if it belongs to the user)
$stmt = $conn->prepare("DELETE FROM compositions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $composition_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Composition deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete composition: ' . $conn->error]);
}

$conn->close();
?>