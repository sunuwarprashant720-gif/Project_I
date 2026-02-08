<?php
session_start();
include("../connect.php");

header('Content-Type: application/json');

// Debug: Log session info
error_log("Session email: " . ($_SESSION['email'] ?? 'not set'));

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Get user ID
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_id = $user['id'];

// Get composition data
$title = $conn->real_escape_string($input['title'] ?? 'My Composition');
$composer = $conn->real_escape_string($input['composer'] ?? 'Anonymous');
$data = json_encode($input['data'] ?? []);

// Check for composition ID
$composition_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($composition_id > 0) {
    // Update existing
    $stmt = $conn->prepare("UPDATE compositions SET title=?, composer=?, data=?, updated_at=NOW() WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $title, $composer, $data, $composition_id, $user_id);
} else {
    // Insert new
    $stmt = $conn->prepare("INSERT INTO compositions (user_id, title, composer, data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $composer, $data);
}

if ($stmt->execute()) {
    $id = $composition_id > 0 ? $composition_id : $conn->insert_id;
    echo json_encode(['success' => true, 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$conn->close();
?>