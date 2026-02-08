<?php
// Start output buffering
ob_start();

session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit();
}

// Include database connection
include("../connect.php");

// Check database connection
if (!$conn || $conn->connect_error) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Get user ID
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_id = $user['id'];

// Prepare data
$title = isset($input['title']) ? trim($input['title']) : 'My Composition';
$composer = isset($input['composer']) ? trim($input['composer']) : 'Anonymous';
$data = isset($input['data']) ? json_encode($input['data']) : json_encode([]);

// Get composition ID
$composition_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Prepare response
$response = [];

try {
    if ($composition_id > 0) {
        // Update existing composition
        $stmt = $conn->prepare("UPDATE compositions SET title=?, composer=?, data=?, updated_at=NOW() WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii", $title, $composer, $data, $composition_id, $user_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'id' => $composition_id, 'message' => 'Composition updated'];
        } else {
            throw new Exception('Update failed');
        }
    } else {
        // Insert new composition
        $stmt = $conn->prepare("INSERT INTO compositions (user_id, title, composer, data) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $composer, $data);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $response = ['success' => true, 'id' => $new_id, 'message' => 'Composition saved'];
        } else {
            throw new Exception('Insert failed');
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

// Clean buffer and send response
ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
exit();
?>