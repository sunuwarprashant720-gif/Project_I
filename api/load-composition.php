<?php
session_start();
include("../connect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$composition_id = $_GET['id'] ?? null;
if (!$composition_id) {
    echo json_encode(['success' => false, 'message' => 'No composition ID provided']);
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

// Get composition
$stmt = $conn->prepare("SELECT title, composer, data FROM compositions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $composition_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$composition = $result->fetch_assoc();

if (!$composition) {
    echo json_encode(['success' => false, 'message' => 'Composition not found']);
    exit();
}

// Return composition data
echo json_encode([
    'success' => true,
    'composition' => [
        'title' => $composition['title'],
        'composer' => $composition['composer'],
        'data' => json_decode($composition['data'], true)
    ]
]);
?>