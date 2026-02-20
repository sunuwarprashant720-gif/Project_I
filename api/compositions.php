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

// Handle GET request (load composition)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    
    $composition_id = intval($_GET['id']);
    error_log("GET request to load composition ID: $composition_id");
    
    // Get user ID
    $email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT user_id AS id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        error_log("User not found for email: $email");
        echo json_encode(['success' => false, 'message' => 'User not found']);
        $conn->close();
        exit();
    }
    
    $user_id = $user['id'];
    error_log("Loading for user_id: $user_id");
    
    // Get composition
    $stmt = $conn->prepare("SELECT id, title, composer, data FROM compositions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $composition_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $composition = $result->fetch_assoc();
    
    if ($composition) {
        error_log("Composition found. Data size: " . strlen($composition['data']) . " bytes");
        error_log("Data preview: " . substr($composition['data'], 0, 100));
        echo json_encode([
            'success' => true,
            'composition' => $composition
        ]);
    } else {
        error_log("Composition not found for ID: $composition_id");
        echo json_encode(['success' => false, 'message' => 'Composition not found']);
    }
    
    $conn->close();
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
$stmt = $conn->prepare("SELECT user_id AS id FROM users WHERE email = ?");
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
        error_log("Updating composition ID: $composition_id for user_id: $user_id");
        error_log("Data size: " . strlen($data) . " bytes");
        
        $stmt = $conn->prepare("UPDATE compositions SET title=?, composer=?, data=?, updated_at=NOW() WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii", $title, $composer, $data, $composition_id, $user_id);
        
        if ($stmt->execute()) {
            error_log("Update successful. Affected rows: " . $stmt->affected_rows);
            $response = ['success' => true, 'id' => $composition_id, 'message' => 'Composition updated'];
        } else {
            error_log("Update failed: " . $stmt->error);
            throw new Exception('Update failed: ' . $stmt->error);
        }
    } else {
        // Insert new composition
        error_log("Inserting new composition for user_id: $user_id");
        error_log("Data size: " . strlen($data) . " bytes");
        
        $stmt = $conn->prepare("INSERT INTO compositions (user_id, title, composer, data) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $composer, $data);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            error_log("Insert successful. New ID: $new_id");
            $response = ['success' => true, 'id' => $new_id, 'message' => 'Composition saved'];
        } else {
            error_log("Insert failed: " . $stmt->error);
            throw new Exception('Insert failed: ' . $stmt->error);
        }
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

// Clean buffer and send response
ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
exit();
?>